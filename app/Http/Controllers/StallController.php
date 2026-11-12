<?php
namespace App\Http\Controllers;

use App\Models\{Building, Registrant, Stall};
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StallController extends Controller
{
    private function rules(?Stall $stall = null): array
    {
        return [
            'stall_number' => ['required', 'string', 'max:30', Rule::unique('stalls', 'stall_number')->ignore($stall)],
            'building_id' => 'required|exists:buildings,id',
            'location' => 'nullable|string|max:120',
            'size_sqm' => 'required|numeric|min:0',
            'rental_rate' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:monthly,daily',
            'stall_type' => 'nullable|string|max:80',
            'shared_capacity' => 'required|integer|min:1|max:20',
            'needs_verification' => 'sometimes|boolean',
            'source_notes' => 'nullable|string|max:2000',
            'status' => 'required|in:available,inactive',
            'description' => 'nullable|string|max:500',
            'registrant_id' => 'nullable|exists:registrants,id',
        ];
    }

    public function index()
    {
        return view('stalls.index', [
            'stalls' => Stall::with(['building', 'owner', 'applications' => fn ($q) => $q->where('status', 'pending')])
                ->when(request('building_id'), fn ($q, $id) => $q->where('building_id', $id))
                ->when(request('q'), fn ($q, $term) => $q->where('stall_number', 'like', '%' . addcslashes($term, '%_') . '%'))
                ->orderBy('building_id')->orderBy('display_order')->orderBy('stall_number')->paginate(20)->withQueryString(),
            'buildings' => Building::orderBy('code')->get(),
        ]);
    }

    public function create()
    {
        return view('stalls.form', ['stall' => new Stall, 'registrants' => Registrant::orderBy('name')->get(), 'buildings' => Building::orderBy('code')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['needs_verification'] = $request->boolean('needs_verification');
        $data['monthly_rate'] = $data['rental_rate'];
        $data['location'] = $data['location'] ?: 'Building ' . Building::findOrFail($data['building_id'])->code;
        $data['status'] = $request->filled('registrant_id') ? 'occupied' : $data['status'];
        DB::transaction(function () use ($data, $request) {
            $stall = Stall::create(Arr::except($data, 'registrant_id'));
            $stall->assignOwner($request->filled('registrant_id') ? (int) $request->registrant_id : null, auth()->id(), true);
        }, 5);
        return redirect()->route('stalls.index')->with('success', 'Stall created.');
    }

    public function edit(Stall $stall)
    {
        return view('stalls.form', ['stall' => $stall, 'registrants' => Registrant::orderBy('name')->get(), 'buildings' => Building::orderBy('code')->get()]);
    }

    public function update(Request $request, Stall $stall)
    {
        $data = $request->validate($this->rules($stall));
        $data['needs_verification'] = $request->boolean('needs_verification');
        $data['monthly_rate'] = $data['rental_rate'];
        $data['location'] = $data['location'] ?: 'Building ' . Building::findOrFail($data['building_id'])->code;
        $data['status'] = $request->filled('registrant_id') ? 'occupied' : $data['status'];
        DB::transaction(function () use ($data, $request, $stall) {
            $lockedStall = Stall::query()->lockForUpdate()->findOrFail($stall->id);
            $lockedStall->update(Arr::except($data, 'registrant_id'));
            $lockedStall->assignOwner($request->filled('registrant_id') ? (int) $request->registrant_id : null, auth()->id(), true);
        }, 5);
        return redirect()->route('stalls.index')->with('success', 'Stall updated. Owner & lease records synced.');
    }

    public function destroy(Stall $stall)
    {
        $deleted = DB::transaction(function () use ($stall) {
            $lockedStall = Stall::query()->lockForUpdate()->findOrFail($stall->id);
            if ($lockedStall->leases()->exists() || $lockedStall->applications()->exists()) return false;
            $lockedStall->delete();
            return true;
        }, 5);
        if (!$deleted) {
            return back()->with('error', 'This stall has historical transactions and cannot be deleted. Set it inactive instead.');
        }
        return redirect()->route('stalls.index')->with('success', 'Stall deleted.');
    }
}
