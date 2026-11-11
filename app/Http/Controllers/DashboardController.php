<?php
namespace App\Http\Controllers;

use App\Models\{Application, Lease, Registrant, Stall};

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return view('dashboard.admin', [
                'stats' => [
                    'Registrants' => Registrant::count(),
                    'Total Stalls' => Stall::count(),
                    'Available' => Stall::where('status', 'available')->count(),
                    'Occupied' => Stall::where('status', 'occupied')->count(),
                    'Pending Applications' => Application::where('status', 'pending')->count(),
                    'Active Leases' => Lease::where('status', 'active')->count(),
                ],
                'recent' => Application::with(['registrant', 'stall'])->latest()->take(6)->get(),
            ]);
        }

        $reg = $user->registrant;
        return view('dashboard.user', [
            'reg' => $reg,
            'stalls' => $reg ? $reg->stalls : collect(),
            'pending' => $reg ? $reg->applications()->where('status', 'pending')->count() : 0,
        ]);
    }
}
