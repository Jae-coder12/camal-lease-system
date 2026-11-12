<?php
namespace App\Http\Controllers;

use App\Models\Lease;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\MarketSystemNotification;

class LeaseController extends Controller
{
    private function authorizeLease(Lease $lease): void
    {
        $u = auth()->user();
        abort_unless($u->isAdmin() || $u->registrant?->id === $lease->registrant_id, 403);
    }

    public function index()
    {
        return view('leases.index', [
            'leases' => Lease::with(['stall.building', 'registrant'])
                ->when(request('building_id'), fn ($q, $id) => $q->whereHas('stall', fn ($s) => $s->where('building_id', $id)))
                ->when(request('status') === 'active', fn ($q) => $q->where('status', 'active')
                    ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today())))
                ->when(request('status') === 'expired', fn ($q) => $q->where(fn ($query) => $query
                    ->where('status', 'expired')->orWhere(fn ($sub) => $sub->where('status', 'active')->whereDate('end_date', '<', today()))))
                ->when(request('status') === 'expiring', fn ($q) => $q->where('status', 'active')->whereBetween('end_date', [today(), today()->addDays(config('market.lease_warning_days', 30))]))
                ->when(request('status') === 'terminated', fn ($q) => $q->whereIn('status', ['ended', 'terminated']))
                ->when(request('status') === 'upcoming', fn ($q) => $q->where('status', 'active')->whereDate('start_date', '>', today()))
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('end_date')->paginate(15)->withQueryString(),
            'buildings' => \App\Models\Building::orderBy('code')->get(),
        ]);
    }

    public function end(Lease $lease)
    {
        DB::transaction(function () use ($lease) {
            $current = Lease::query()->lockForUpdate()->findOrFail($lease->id);
            if ($current->status !== 'active') {
                return;
            }
            $before = $current->only(['status', 'start_date', 'end_date', 'rental_amount', 'payment_frequency']);
            $current->update([
                'status' => 'terminated',
                'end_date' => today(),
                'terminated_at' => now(),
                'terminated_by' => auth()->id(),
            ]);
            $stall = $current->stall()->lockForUpdate()->firstOrFail();
            DB::table('stall_occupants')->where('lease_id', $current->id)->update([
                'end_date' => today()->toDateString(),
                'updated_at' => now(),
            ]);
            $nextLease = $stall->leases()->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
                ->where('id', '!=', $current->id)->orderBy('id')->first();
            $stall->forceFill([
                'registrant_id' => $nextLease?->registrant_id,
                'status' => $nextLease || ($stall->source_occupant_name && $stall->shared_capacity > 1) ? 'occupied' : 'available',
            ])->save();
            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'lease.terminated',
                'subject_type' => Lease::class,
                'subject_id' => $current->id,
                'details' => json_encode(['stall_id' => $current->stall_id, 'before' => $before, 'after' => $current->only(['status', 'end_date', 'terminated_at'])]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 5);
        return back()->with('success', 'Lease ended. Stall is available again.');
    }

    public function renew(Request $request, Lease $lease)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'rental_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_frequency' => ['required', 'in:monthly,daily'],
            'contract_terms' => ['nullable', 'string', 'max:12000'],
        ]);

        $renewed = DB::transaction(function () use ($lease, $data) {
            $current = Lease::query()->lockForUpdate()->findOrFail($lease->id);
            if (!in_array($current->status, ['active', 'expired'], true) || $current->renewal()->exists()) return false;

            $stall = $current->stall()->lockForUpdate()->firstOrFail();
            $start = \Illuminate\Support\Carbon::parse($data['start_date']);
            if ($current->end_date && $current->end_date->greaterThanOrEqualTo(today())
                && !$start->gt($current->end_date)) {
                return false;
            }
            if ($current->stall_id !== $stall->id) return false;

            $otherActiveLease = Lease::where('stall_id', $stall->id)->where('status', 'active')
                ->where('id', '!=', $current->id)
                ->where('registrant_id', $current->registrant_id)
                ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', $start->toDateString()))
                ->exists();
            if ($otherActiveLease) return false;

            $before = $current->only(['status', 'start_date', 'end_date', 'rental_amount', 'payment_frequency']);
            $current->update(['status' => 'ended']);
            DB::table('stall_occupants')->where('lease_id', $current->id)->update([
                'end_date' => $current->end_date?->toDateString() ?? $start->copy()->subDay()->toDateString(),
                'updated_at' => now(),
            ]);

            $newLease = Lease::create([
                'stall_id' => $stall->id,
                'registrant_id' => $current->registrant_id,
                'application_id' => $current->application_id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'monthly_rate' => $data['rental_amount'],
                'rental_amount' => $data['rental_amount'],
                'payment_frequency' => $data['payment_frequency'],
                'approved_by' => auth()->id(),
                'renewed_from_lease_id' => $current->id,
                'contract_terms' => ($data['contract_terms'] ?? null) ?: $current->contract_terms,
                'status' => 'active',
            ]);
            $stall->occupants()->attach($current->registrant_id, [
                'lease_id' => $newLease->id,
                'start_date' => $newLease->start_date->toDateString(),
            ]);
            $stall->forceFill([
                'registrant_id' => $current->registrant_id,
                'status' => 'occupied',
                'monthly_rate' => $data['rental_amount'],
                'rental_rate' => $data['rental_amount'],
                'payment_frequency' => $data['payment_frequency'],
            ])->save();

            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'lease.renewed',
                'subject_type' => Lease::class,
                'subject_id' => $newLease->id,
                'details' => json_encode([
                    'previous_lease_id' => $current->id,
                    'previous_contract' => $current->contract_number,
                    'new_contract' => $newLease->contract_number,
                    'before' => $before,
                    'after' => $newLease->only(['start_date', 'end_date', 'rental_amount', 'payment_frequency']),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $current->registrant->user?->notify(new MarketSystemNotification(
                'Lease renewed',
                'Your lease for stall ' . $stall->stall_number . ' has been renewed under contract ' . $newLease->contract_number . '.',
                route('my.leases'),
            ));

            return true;
        }, 5);

        return back()->with($renewed ? 'success' : 'error', $renewed
            ? 'A new lease contract was created and the previous contract was preserved.'
            : 'This lease cannot be renewed in its current state or the requested dates conflict.');
    }

    public function update(Request $request, Lease $lease)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'rental_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_frequency' => ['required', 'in:monthly,daily'],
            'contract_terms' => ['nullable', 'string', 'max:12000'],
        ]);
        DB::transaction(function () use ($lease, $data) {
            $current = Lease::query()->lockForUpdate()->findOrFail($lease->id);
            $before = $current->only(['start_date', 'end_date', 'rental_amount', 'payment_frequency', 'contract_terms']);
            $current->update($data + ['monthly_rate' => $data['rental_amount']]);
            if ($current->status === 'active') {
                $current->stall()->update([
                    'monthly_rate' => $data['rental_amount'],
                    'rental_rate' => $data['rental_amount'],
                    'payment_frequency' => $data['payment_frequency'],
                ]);
            }

            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'lease.updated',
                'subject_type' => Lease::class,
                'subject_id' => $current->id,
                'details' => json_encode(['before' => $before, 'after' => $current->only(array_keys($before))]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 5);

        return back()->with('success', 'Lease information updated and recorded in the activity log.');
    }

    public function document(Lease $lease)
    {
        $this->authorizeLease($lease);
        return view('leases.document', ['lease' => $lease->load(['stall', 'registrant']), 'pdf' => false]);
    }

    public function download(Lease $lease)
    {
        $this->authorizeLease($lease);
        $lease->load(['stall', 'registrant']);
        return Pdf::loadView('leases.document', ['lease' => $lease, 'pdf' => true])
            ->setPaper('a4')->download('Lease-Contract-' . $lease->contract_number . '.pdf');
    }
}
