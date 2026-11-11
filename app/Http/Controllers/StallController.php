<?php
namespace App\Http\Controllers;

use App\Models\{Registrant, Stall};
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StallController extends Controller
{
    private function rules(?Stall $stall = null): array
    {
        return [
            'stall_number' => 'required|string|max:30|unique:stalls,stall_number,' . ($stall?->id ?? 'NULL'),
            'location' => 'required|string|max:120',
            'size_sqm' => 'required|numeric|min:0',
            'monthly_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'registrant_id' => 'nullable|exists:registrants,id',
        ];
    }

    public function index()
    {
        return view('stalls.index', ['stalls' => Stall::with('owner')->orderBy('stall_number')->paginate(12)]);
    }

    public function create()
    {
        return view('stalls.form', ['stall' => new Stall, 'registrants' => Registrant::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $stall = Stall::create(Arr::except($data, 'registrant_id'));
        $stall->assignOwner($request->filled('registrant_id') ? (int) $request->registrant_id : null);
        return redirect()->route('stalls.index')->with('success', 'Stall created.');
    }

    public function edit(Stall $stall)
    {
        return view('stalls.form', ['stall' => $stall, 'registrants' => Registrant::orderBy('name')->get()]);
    }

    public function update(Request $request, Stall $stall)
    {
        $data = $request->validate($this->rules($stall));
        $stall->update(Arr::except($data, 'registrant_id'));
        // Changing the owner auto-ends the old lease, creates a new one and updates status.
        $stall->assignOwner($request->filled('registrant_id') ? (int) $request->registrant_id : null);
        return redirect()->route('stalls.index')->with('success', 'Stall updated. Owner & lease records synced.');
    }

    public function destroy(Stall $stall)
    {
        $stall->delete();
        return redirect()->route('stalls.index')->with('success', 'Stall deleted.');
    }
}
