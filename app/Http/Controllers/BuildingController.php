<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BuildingController extends Controller
{
    public function index()
    {
        return view('buildings.index', ['buildings' => Building::withCount('stalls')->orderBy('code')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:buildings,code'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        Building::create($data);

        return back()->with('success', 'Building added.');
    }

    public function update(Request $request, Building $building)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('buildings', 'code')->ignore($building)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);
        $building->update($data);

        return back()->with('success', 'Building updated.');
    }

    public function destroy(Building $building)
    {
        if ($building->stalls()->exists()) {
            return back()->with('error', 'A building with stall records cannot be deleted. Deactivate it instead.');
        }
        $building->delete();

        return back()->with('success', 'Building deleted.');
    }
}
