<?php
namespace App\Http\Controllers;

use App\Models\Registrant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrantController extends Controller
{
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'status' => 'required|in:Single,Married,Widowed,Separated,Divorced',
            'citizenship' => 'required|string|max:80',
            'birthdate' => 'required|date|before_or_equal:' . today()->subYears(18)->toDateString(),
            'address' => 'required|string|max:255',
            'contact_number' => ['required', 'regex:/^[0-9+\-\s()]{7,20}$/'],
        ];
    }

    public function index(Request $request)
    {
        $q = $request->q;
        $registrants = Registrant::when($q, fn ($x) => $x->where(fn ($w) => $w
                ->where('name', 'like', "%$q%")->orWhere('control_number', 'like', "%$q%")))
            ->orderBy('control_number')->paginate(10)->withQueryString();
        return view('registrants.index', compact('registrants', 'q'));
    }

    public function create()
    {
        return view('registrants.form', ['registrant' => new Registrant, 'control' => 'Assigned when saved']);
    }

    public function store(Request $request)
    {
        $registrant = Registrant::create($request->validate(self::rules()));
        DB::table('activity_logs')->insert([
            'user_id' => auth()->id(), 'action' => 'registrant.created',
            'subject_type' => Registrant::class, 'subject_id' => $registrant->id,
            'details' => json_encode(['control_number' => $registrant->control_number]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('registrants.index')->with('success', 'Registrant added.');
    }

    public function edit(Registrant $registrant)
    {
        return view('registrants.form', ['registrant' => $registrant, 'control' => $registrant->control_number]);
    }

    public function show(Registrant $registrant)
    {
        return view('registrants.show', [
            'registrant' => $registrant->load(['user', 'applications.stall.building', 'leases.stall.building']),
        ]);
    }

    public function update(Request $request, Registrant $registrant)
    {
        $registrant->update($request->validate(self::rules()));
        return redirect()->route('registrants.index')->with('success', 'Registrant updated.');
    }

    public function destroy(Registrant $registrant)
    {
        $deleted = DB::transaction(function () use ($registrant) {
            $lockedRegistrant = Registrant::query()->lockForUpdate()->findOrFail($registrant->id);
            if ($lockedRegistrant->leases()->exists() || $lockedRegistrant->applications()->exists()) return false;
            $lockedRegistrant->delete();
            return true;
        }, 5);
        if (!$deleted) {
            return back()->with('error', 'This registrant has historical records and cannot be deleted.');
        }
        return redirect()->route('registrants.index')->with('success', 'Registrant deleted.');
    }
}
