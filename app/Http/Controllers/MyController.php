<?php
namespace App\Http\Controllers;

use App\Models\{Application, Stall};
use App\Notifications\MarketSystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MyController extends Controller
{
    public function stalls()
    {
        $reg = auth()->user()->registrant;
        $stalls = Stall::with(['building', 'owner', 'activeLease', 'applications' => fn ($q) => $q->where('status', 'pending')])
            ->whereHas('building', fn ($query) => $query->where('is_active', true))
            ->where('status', '!=', 'inactive')->orderBy('building_id')->orderBy('display_order')->orderBy('stall_number')
            ->paginate(30)->withQueryString();
        return view('my.stalls', compact('stalls', 'reg'));
    }

    public function apply(Request $request, Stall $stall)
    {
        $reg = auth()->user()->registrant;
        abort_unless($reg, 403, 'No registration profile found.');
        $data = $request->validate([
            'purpose' => 'required|string|max:150',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::transaction(function () use ($request, $stall, $reg, $data) {
            $lockedStall = Stall::query()->lockForUpdate()->findOrFail($stall->id);
            if ($reg->applications()->where('stall_id', $lockedStall->id)->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages(['stall' => 'You already have a pending application for this stall.']);
            }
            if ($reg->leases()->where('stall_id', $lockedStall->id)->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))->exists()) {
                throw ValidationException::withMessages(['stall' => 'You already hold an active lease for this stall.']);
            }
            $lockedStall->closeExpiredLeases();
            $lockedStall->refresh();
            if (!$lockedStall->canReceiveApplication()) {
                throw ValidationException::withMessages(['stall' => 'This stall is unavailable or already reserved by another application.']);
            }

            $document = $request->file('supporting_document')?->store('application-documents', 'local');
            $application = Application::create([
                'registrant_id' => $reg->id,
                'stall_id' => $lockedStall->id,
                'purpose' => $data['purpose'],
                'supporting_document' => $document,
            ]);
            auth()->user()->notify(new MarketSystemNotification(
                'Application submitted',
                'Your application for stall ' . $lockedStall->stall_number . ' is pending review.',
                route('my.applications'),
            ));

            DB::table('activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'application.submitted',
                'subject_type' => Application::class,
                'subject_id' => $application->id,
                'details' => json_encode(['stall_id' => $lockedStall->id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            \App\Models\User::where('role', 'admin')->get()->each(fn ($admin) => $admin->notify(
                new MarketSystemNotification(
                    'New stall application',
                    $reg->name . ' applied for stall ' . $lockedStall->stall_number . '.',
                    route('applications.index'),
                ),
            ));
        }, 5);
        return redirect()->route('my.applications')->with('success', 'Application submitted for stall ' . $stall->stall_number . '.');
    }

    public function applications()
    {
        $reg = auth()->user()->registrant;
        return view('my.applications', ['apps' => $reg ? $reg->applications()->with('stall')->latest()->get() : collect()]);
    }

    public function cancelApplication(Application $application)
    {
        abort_unless($application->registrant_id === auth()->user()->registrant?->id, 404);
        $cancelled = DB::transaction(function () use ($application) {
            $locked = Application::query()->lockForUpdate()->findOrFail($application->id);
            if ($locked->status !== 'pending') return false;
            $locked->transitionTo('cancelled', 'Cancelled by applicant.');
            return true;
        }, 5);
        if (!$cancelled) return back()->with('error', 'Only pending applications can be cancelled.');

        return back()->with('success', 'Application cancelled.');
    }

    public function leases()
    {
        $reg = auth()->user()->registrant;
        return view('my.leases', ['leases' => $reg ? $reg->leases()->with(['stall.building', 'payments'])->latest()->get() : collect()]);
    }

    public function updateProfile(Request $request)
    {
        $registrant = auth()->user()->registrant;
        abort_unless($registrant, 403, 'No applicant profile is attached to this account.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
            'citizenship' => ['required', 'string', 'max:80'],
            'birthdate' => ['required', 'date', 'before_or_equal:' . today()->subYears(18)->toDateString()],
            'address' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
        ], ['birthdate.before_or_equal' => 'You must be at least 18 years old.']);

        DB::transaction(function () use ($data, $registrant) {
            $registrant->update(collect($data)->except('email')->all());
            auth()->user()->update(['name' => $data['name'], 'email' => $data['email']]);
        });

        return back()->with('success', 'Your profile has been updated.');
    }
}
