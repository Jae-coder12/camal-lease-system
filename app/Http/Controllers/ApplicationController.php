<?php
namespace App\Http\Controllers;

use App\Models\{Application, Stall};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $apps = Application::with(['registrant', 'stall'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(10)->withQueryString();
        return view('applications.index', compact('apps'));
    }

    public function approve(Request $request, Application $application)
    {
        if ($application->status !== 'pending') return back()->with('error', 'Application already processed.');

        $ok = DB::transaction(function () use ($application, $request) {
            $stall = Stall::lockForUpdate()->find($application->stall_id);
            if ($stall->status !== 'available') return false;

            $stall->assignOwner($application->registrant_id);   // auto-updates owner + creates lease
            $application->update(['status' => 'approved', 'remarks' => $request->remarks, 'reviewed_at' => now()]);
            Application::where('stall_id', $stall->id)->where('status', 'pending')
                ->update(['status' => 'rejected', 'remarks' => 'Stall awarded to another applicant.', 'reviewed_at' => now()]);
            return true;
        });

        return back()->with($ok ? 'success' : 'error', $ok ? 'Approved. Stall owner and lease updated.' : 'Stall is no longer available.');
    }

    public function reject(Request $request, Application $application)
    {
        if ($application->status === 'pending') {
            $application->update(['status' => 'rejected', 'remarks' => $request->remarks, 'reviewed_at' => now()]);
        }
        return back()->with('success', 'Application rejected.');
    }
}
