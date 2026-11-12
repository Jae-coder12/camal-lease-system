<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Notifications\MarketSystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending,paid,rejected'],
            'method' => ['nullable', 'in:cash,bank_transfer,check,other'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return view('payments.index', [
            'payments' => Payment::with(['lease.stall.building', 'lease.registrant', 'recorder'])
                ->when($filters['q'] ?? null, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('reference_number', 'like', '%' . $search . '%')
                            ->orWhereHas('lease.registrant', fn ($registrants) => $registrants
                                ->where('name', 'like', '%' . $search . '%')
                                ->orWhere('control_number', 'like', '%' . $search . '%'));
                    });
                })
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['method'] ?? null, fn ($query, $method) => $query->where('method', $method))
                ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('payment_date', '>=', $date))
                ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('payment_date', '<=', $date))
                ->latest('payment_date')->paginate(15)->withQueryString(),
            'leases' => Lease::with(['stall', 'registrant'])->where('status', 'active')
                ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
                ->orderByDesc('id')->get(),
        ]);
    }

    public function review(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'remarks' => ['nullable', 'required_if:decision,reject', 'string', 'max:1000'],
        ]);

        $reviewedPayment = DB::transaction(function () use ($payment, $data) {
            $current = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($current->status !== 'pending') {
                return null;
            }

            $status = $data['decision'] === 'approve' ? 'paid' : 'rejected';
            $current->update([
                'status' => $status,
                'remarks' => $data['remarks'] ?? $current->remarks,
            ]);

            return $current->fresh(['lease.registrant.user', 'lease.stall']);
        }, 5);

        if (!$reviewedPayment) {
            return back()->with('error', 'This transaction is no longer pending review.');
        }

        DB::table('activity_logs')->insert([
            'user_id' => auth()->id(),
            'action' => 'payment.' . ($reviewedPayment->status === 'paid' ? 'approved' : 'rejected'),
            'subject_type' => Payment::class,
            'subject_id' => $reviewedPayment->id,
            'details' => json_encode([
                'reference_number' => $reviewedPayment->reference_number,
                'status' => $reviewedPayment->status,
                'remarks' => $reviewedPayment->remarks,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reviewedPayment->lease->registrant->user?->notify(new MarketSystemNotification(
            $reviewedPayment->status === 'paid' ? 'Rental payment verified' : 'Rental payment rejected',
            $reviewedPayment->status === 'paid'
                ? 'Payment ' . $reviewedPayment->reference_number . ' has been verified.'
                : 'Payment ' . $reviewedPayment->reference_number . ' was rejected. ' . $reviewedPayment->remarks,
            $reviewedPayment->status === 'paid' ? route('payments.receipt', $reviewedPayment) : route('my.leases'),
        ));

        return back()->with('success', $reviewedPayment->status === 'paid'
            ? 'Payment approved and marked as paid.'
            : 'Payment rejected.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lease_id' => ['required', 'exists:leases,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'period_covered' => ['nullable', 'string', 'max:80'],
            'method' => ['nullable', 'in:cash,bank_transfer,check,other'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);
        $lease = Lease::findOrFail($data['lease_id']);
        if ($lease->status !== 'active' || ($lease->end_date && $lease->end_date->lt(today()))) {
            return back()->withInput()->withErrors(['lease_id' => 'Payments can only be recorded for an active lease.']);
        }

        $payment = Payment::create($data + [
            'recorded_by' => auth()->id(),
            'reference_number' => 'PM-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
            'status' => 'paid',
        ]);
        DB::table('activity_logs')->insert([
            'user_id' => auth()->id(),
            'action' => 'payment.recorded',
            'subject_type' => Payment::class,
            'subject_id' => $payment->id,
            'details' => json_encode(['reference_number' => $payment->reference_number, 'amount' => $payment->amount]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $lease->registrant->user?->notify(new \App\Notifications\MarketSystemNotification(
            'Rental payment recorded',
            'Payment ' . $payment->reference_number . ' was recorded for stall ' . $lease->stall->stall_number . '.',
            route('payments.receipt', $payment),
        ));

        return redirect()->route('payments.receipt', $payment)->with('success', 'Payment recorded. Receipt is ready to print.');
    }

    public function receipt(Payment $payment)
    {
        abort_unless($payment->status === 'paid', 404);
        abort_unless(
            auth()->user()->isAdmin() || auth()->user()->registrant?->id === $payment->lease->registrant_id,
            403,
        );

        return view('payments.receipt', [
            'payment' => $payment->load(['lease.stall.building', 'lease.registrant', 'recorder']),
        ]);
    }
}
