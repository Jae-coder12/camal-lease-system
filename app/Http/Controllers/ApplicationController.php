<?php
namespace App\Http\Controllers;

use App\Models\{Application, Lease, Stall};
use App\Notifications\MarketSystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $apps = Application::with(['registrant', 'stall'])
            ->with(['reviewer', 'lease', 'statusHistories.actor'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->control_number, fn ($q, $number) => $q->whereHas('registrant', fn ($r) => $r->where('control_number', $number)))
            ->when($request->building_id, fn ($q, $id) => $q->whereHas('stall', fn ($s) => $s->where('building_id', $id)))
            ->when($request->stall_number, fn ($q, $number) => $q->whereHas('stall', fn ($s) => $s->where('stall_number', $number)))
            ->latest()->paginate(10)->withQueryString();
        return view('applications.index', compact('apps'));
    }

    public function beginReview(Application $application)
    {
        $changed = DB::transaction(function () use ($application) {
            $locked = Application::query()->lockForUpdate()->findOrFail($application->id);
            if ($locked->status !== 'pending') return false;
            $locked->transitionTo('under_review', 'Review started.', auth()->id());
            return true;
        }, 5);

        return back()->with($changed ? 'success' : 'error', $changed ? 'Application moved to under review.' : 'Application has already been processed.');
    }

    public function approve(Request $request, Application $application)
    {
        $data = $request->validate(['remarks' => 'nullable|string|max:2000']);
        $ok = DB::transaction(function () use ($application, $data) {
            $lockedApplication = Application::query()->lockForUpdate()->findOrFail($application->id);
            if (!in_array($lockedApplication->status, ['pending', 'under_review'], true)) return false;

            $stall = Stall::query()->lockForUpdate()->findOrFail($lockedApplication->stall_id);
            $stall->closeExpiredLeases();
            $stall->refresh();
            if ($stall->status === 'inactive' || $stall->needs_verification
                || !$stall->building()->where('is_active', true)->exists()
                || $stall->occupiedCapacity() >= $stall->shared_capacity) {
                return false;
            }
            if (Lease::where('stall_id', $stall->id)->where('registrant_id', $lockedApplication->registrant_id)
                ->where('status', 'active')->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))->exists()) {
                return false;
            }

            if ($stall->shared_capacity > 1) {
                $stall->addSharedOccupant($lockedApplication->registrant_id, auth()->id());
            } else {
                $stall->assignOwner($lockedApplication->registrant_id, auth()->id());
            }
            $lockedApplication->transitionTo('approved', $data['remarks'] ?? null, auth()->id());
            $lease = Lease::where('stall_id', $stall->id)->where('registrant_id', $lockedApplication->registrant_id)
                ->where('status', 'active')->latest('id')->firstOrFail();
            $lease->update(['application_id' => $lockedApplication->id]);
            $lockedApplication->update(['lease_id' => $lease->id]);

            $competitors = collect();
            if ($stall->occupiedCapacity() >= $stall->shared_capacity) {
                $competitors = Application::where('stall_id', $stall->id)
                    ->whereIn('status', ['pending', 'under_review'])->where('id', '!=', $lockedApplication->id)->get();
                foreach ($competitors as $competitor) {
                    $competitor->transitionTo(
                        'rejected',
                        'Closed because the stall has no remaining shared-occupancy capacity.',
                        auth()->id(),
                    );
                }
            }
            foreach ($competitors as $competitor) {
                $competitor->registrant->user?->notify(new MarketSystemNotification(
                    'Application closed',
                    'Another applicant was awarded stall ' . $stall->stall_number . '.',
                    route('my.applications'),
                ));
            }

            $lockedApplication->registrant->user?->notify(new MarketSystemNotification(
                'Application approved',
                'Your application for stall ' . $stall->stall_number . ' has been approved and a lease was created.',
                route('my.leases'),
            ));
            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'application.approved',
                'subject_type' => Application::class,
                'subject_id' => $lockedApplication->id,
                'details' => json_encode(['stall_id' => $stall->id, 'registrant_id' => $lockedApplication->registrant_id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return true;
        }, 5);

        return back()->with($ok ? 'success' : 'error', $ok ? 'Approved. Stall owner and lease updated.' : 'Stall is no longer available.');
    }

    public function reject(Request $request, Application $application)
    {
        $data = $request->validate(['remarks' => 'required|string|max:2000']);
        $rejectedApplication = DB::transaction(function () use ($application, $data) {
            $locked = Application::query()->lockForUpdate()->findOrFail($application->id);
            if (!in_array($locked->status, ['pending', 'under_review'], true)) return null;
            $locked->transitionTo('rejected', $data['remarks'], auth()->id());
            return $locked->load(['registrant.user', 'stall']);
        }, 5);
        if (!$rejectedApplication) return back()->with('error', 'Application has already been processed.');

        $rejectedApplication->registrant->user?->notify(new MarketSystemNotification(
            'Application rejected',
            'Your application for stall ' . $rejectedApplication->stall->stall_number . ' was not approved.',
            route('my.applications'),
        ));
        DB::table('activity_logs')->insert([
            'user_id' => auth()->id(),
            'action' => 'application.rejected',
            'subject_type' => Application::class,
            'subject_id' => $rejectedApplication->id,
            'details' => json_encode(['stall_id' => $rejectedApplication->stall_id]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Application rejected.');
    }

    public function complete(Application $application)
    {
        $completed = DB::transaction(function () use ($application) {
            $locked = Application::query()->lockForUpdate()->findOrFail($application->id);
            if ($locked->status !== 'approved' || !$locked->lease_id) return false;
            $locked->transitionTo('completed', 'Lease contract issued and transaction completed.', auth()->id());
            return true;
        }, 5);

        return back()->with($completed ? 'success' : 'error', $completed
            ? 'Transaction marked completed.'
            : 'Only an approved application with a generated lease contract can be completed.');
    }

    public function document(Application $application)
    {
        abort_unless($application->supporting_document, 404, 'No supporting document was uploaded.');
        abort_unless(Storage::disk('local')->exists($application->supporting_document), 404, 'Supporting document is unavailable.');

        return Storage::disk('local')->download($application->supporting_document);
    }
}
