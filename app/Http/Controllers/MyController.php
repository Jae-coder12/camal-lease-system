<?php
namespace App\Http\Controllers;

use App\Models\{Application, Stall};
use Illuminate\Http\Request;

class MyController extends Controller
{
    public function stalls()
    {
        $reg = auth()->user()->registrant;
        $pendingIds = $reg ? $reg->applications()->where('status', 'pending')->pluck('stall_id')->all() : [];
        return view('my.stalls', ['stalls' => Stall::orderBy('stall_number')->get(), 'pendingIds' => $pendingIds]);
    }

    public function apply(Request $request, Stall $stall)
    {
        $reg = auth()->user()->registrant;
        abort_unless($reg, 403, 'No registration profile found.');
        $data = $request->validate(['purpose' => 'required|string|max:150']);

        if ($stall->status !== 'available') return back()->with('error', 'Stall is not available.');
        if ($reg->applications()->where('stall_id', $stall->id)->where('status', 'pending')->exists())
            return back()->with('error', 'You already have a pending application for this stall.');

        Application::create(['registrant_id' => $reg->id, 'stall_id' => $stall->id, 'purpose' => $data['purpose']]);
        return redirect()->route('my.applications')->with('success', 'Application submitted for stall ' . $stall->stall_number . '.');
    }

    public function applications()
    {
        $reg = auth()->user()->registrant;
        return view('my.applications', ['apps' => $reg ? $reg->applications()->with('stall')->latest()->get() : collect()]);
    }

    public function leases()
    {
        $reg = auth()->user()->registrant;
        return view('my.leases', ['leases' => $reg ? $reg->leases()->with('stall')->latest()->get() : collect()]);
    }
}
