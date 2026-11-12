<?php
namespace App\Http\Controllers;

use App\Models\{Application, Building, Lease, Payment, Registrant, Stall};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $buildings = Building::withCount([
                'stalls',
                'stalls as occupied_stalls_count' => fn ($q) => $q->where('status', 'occupied'),
                'stalls as available_stalls_count' => fn ($q) => $q->where('status', 'available')->where('needs_verification', false)
                    ->whereDoesntHave('applications', fn ($apps) => $apps->where('status', 'pending')),
            ])->where('is_active', true)->orderBy('code')->get();
            $months = collect(range(5, 0))->map(fn ($offset) => now()->startOfMonth()->subMonths($offset));
            $income = $months->map(fn ($month) => (float) Payment::where('status', 'paid')
                ->whereBetween('payment_date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->sum('amount'));
            $expiringLeases = Lease::with(['stall.building', 'registrant'])
                ->where('status', 'active')
                ->whereBetween('end_date', [today(), today()->addDays(config('market.lease_warning_days', 30))])
                ->orderBy('end_date')->limit(6)->get();
            $monitoringLeaseCounts = [
                Lease::where('status', 'active')->where(fn ($q) => $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>', today()->addDays(config('market.lease_warning_days', 30))))->count(),
                Lease::where('status', 'active')->whereBetween('end_date', [today(), today()->addDays(config('market.lease_warning_days', 30))])->count(),
                Lease::where(fn ($q) => $q->where('status', 'expired')
                    ->orWhere(fn ($active) => $active->where('status', 'active')->whereDate('end_date', '<', today())))->count(),
            ];

            return view('dashboard.admin', [
                'stats' => [
                    'Total Buildings' => Building::where('is_active', true)->count(),
                    'Total Market Stalls' => Stall::count(),
                    'Occupied Stalls' => Stall::where('status', 'occupied')->count(),
                    'Available Stalls' => Stall::where('status', 'available')->where('needs_verification', false)
                        ->whereDoesntHave('applications', fn ($q) => $q->where('status', 'pending'))->count(),
                    'Pending Applications' => Application::where('status', 'pending')->count(),
                    'Pending Transactions' => \App\Models\Payment::where('status', 'pending')->count(),
                    'Applications Under Review' => Application::where('status', 'under_review')->count(),
                    'Approved Applications' => Application::where('status', 'approved')->count(),
                    'Rejected Applications' => Application::where('status', 'rejected')->count(),
                    'Completed Transactions' => Payment::where('status', 'paid')->count(),
                    'Active Lease Contracts' => Lease::where('status', 'active')->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))->count(),
                    'Expiring Lease Contracts' => Lease::where('status', 'active')->whereBetween('end_date', [today(), today()->addDays(config('market.lease_warning_days', 30))])->count(),
                    'Expired Lease Contracts' => Lease::where(fn ($query) => $query->where('status', 'expired')
                        ->orWhere(fn ($active) => $active->where('status', 'active')->whereDate('end_date', '<', today())))->count(),
                    'Total Registrants' => Registrant::count(),
                ],
                'recent' => Application::with(['registrant', 'stall.building'])->latest()->take(8)->get(),
                'recentPayments' => Payment::with(['lease.stall.building', 'lease.registrant', 'recorder'])
                    ->latest('payment_date')->latest('id')->limit(6)->get(),
                'recentRegistrants' => Registrant::with('user')->latest()->limit(6)->get(),
                'expiringLeases' => $expiringLeases,
                'leaseMonitoringCounts' => $monitoringLeaseCounts,
                'leaseMonitoringTotal' => array_sum($monitoringLeaseCounts),
                'buildings' => $buildings,
                'monthlyLabels' => $months->map(fn ($month) => $month->format('M Y'))->all(),
                'monthlyIncome' => $income->all(),
                'applicationCounts' => [
                    Application::where('status', 'pending')->count(),
                    Application::where('status', 'under_review')->count(),
                    Application::where('status', 'approved')->count(),
                    Application::where('status', 'rejected')->count(),
                    Application::where('status', 'completed')->count(),
                ],
                'activities' => DB::table('activity_logs')
                    ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                    ->select('activity_logs.*', 'users.name as actor_name')
                    ->orderByDesc('activity_logs.created_at')->limit(8)->get(),
                'notifications' => $user->notifications()->latest()->limit(6)->get(),
                'unreadNotifications' => $user->unreadNotifications()->count(),
            ]);
        }

        $reg = $user->registrant;
        $applications = $reg
            ? $reg->applications()->with(['stall.building', 'lease', 'statusHistories'])->latest()->get()
            : collect();
        $leases = $reg
            ? $reg->leases()->with(['stall.building'])->latest()->get()
            : collect();
        $payments = $reg
            ? Payment::with(['lease.stall.building'])
                ->whereHas('lease', fn ($query) => $query->where('registrant_id', $reg->id))
                ->latest('payment_date')->latest('id')->limit(8)->get()
            : collect();
        $activeLeases = $leases->filter(fn (Lease $lease) => $lease->status === 'active'
            && (!$lease->end_date || !$lease->end_date->lt(today())));

        return view('dashboard.user', [
            'reg' => $reg,
            'stalls' => $activeLeases,
            'leases' => $leases,
            'applications' => $applications,
            'payments' => $payments,
            'applicationCounts' => [
                'total' => $applications->count(),
                'pending' => $applications->whereIn('status', ['pending', 'under_review'])->count(),
                'approved' => $applications->where('status', 'approved')->count(),
                'rejected' => $applications->where('status', 'rejected')->count(),
            ],
            'paymentCounts' => [
                'pending' => $reg ? Payment::whereHas('lease', fn ($query) => $query->where('registrant_id', $reg->id))
                    ->where('status', 'pending')->count() : 0,
                'completed' => $reg ? Payment::whereHas('lease', fn ($query) => $query->where('registrant_id', $reg->id))
                    ->where('status', 'paid')->count() : 0,
            ],
            'notifications' => $user->notifications()->latest()->limit(8)->get(),
            'unreadNotifications' => $user->unreadNotifications()->count(),
        ]);
    }
}
