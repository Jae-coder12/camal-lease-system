<?php
namespace App\Http\Controllers;

use App\Models\Lease;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaseController extends Controller
{
    private function authorizeLease(Lease $lease): void
    {
        $u = auth()->user();
        abort_unless($u->isAdmin() || $u->registrant?->id === $lease->registrant_id, 403);
    }

    public function index()
    {
        return view('leases.index', ['leases' => Lease::with(['stall', 'registrant'])->latest()->paginate(12)]);
    }

    public function end(Lease $lease)
    {
        $lease->stall->assignOwner(null);   // ends lease + frees stall
        return back()->with('success', 'Lease ended. Stall is available again.');
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
