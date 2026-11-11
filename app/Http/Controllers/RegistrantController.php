<?php
namespace App\Http\Controllers;

use App\Models\Registrant;
use Illuminate\Http\Request;

class RegistrantController extends Controller
{
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'status' => 'required|in:Single,Married,Widowed,Separated,Divorced',
            'citizenship' => 'required|string|max:80',
            'birthdate' => 'required|date|before:today',
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
        return view('registrants.form', ['registrant' => new Registrant, 'control' => Registrant::nextControlNumber()]);
    }

    public function store(Request $request)
    {
        Registrant::create($request->validate(self::rules()));
        return redirect()->route('registrants.index')->with('success', 'Registrant added.');
    }

    public function edit(Registrant $registrant)
    {
        return view('registrants.form', ['registrant' => $registrant, 'control' => $registrant->control_number]);
    }

    public function update(Request $request, Registrant $registrant)
    {
        $registrant->update($request->validate(self::rules()));
        return redirect()->route('registrants.index')->with('success', 'Registrant updated.');
    }

    public function destroy(Registrant $registrant)
    {
        $registrant->delete();
        return redirect()->route('registrants.index')->with('success', 'Registrant deleted. Their stalls are now available.');
    }
}
