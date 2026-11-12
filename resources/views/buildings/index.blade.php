@extends('layouts.app')
@section('title','Market Buildings')
@section('content')
<div class="row g-3">
<div class="col-xl-4"><div class="card shadow-sm"><div class="card-header bg-white fw-semibold">Add building</div><div class="card-body">
<form method="POST" action="{{ route('buildings.store') }}">@csrf
<div class="mb-3"><label class="form-label">Building code</label><input name="code" maxlength="10" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control"></textarea></div>
<button class="btn btn-primary">Add building</button></form></div></div></div>
<div class="col-xl-8"><div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Building</th><th>Stalls</th><th>Status</th><th>Update</th><th></th></tr></thead><tbody>
@forelse($buildings as $building)<tr><td><strong>{{ $building->name }}</strong><div class="small text-muted">{{ $building->description }}</div></td><td>{{ $building->stalls_count }}</td><td><span class="badge bg-{{ $building->is_active?'success':'secondary' }}">{{ $building->is_active?'Active':'Inactive' }}</span></td>
<td><form method="POST" action="{{ route('buildings.update',$building) }}" class="d-flex flex-column gap-1">@csrf @method('PUT')
<input name="code" class="form-control form-control-sm" value="{{ $building->code }}" required><input name="name" class="form-control form-control-sm" value="{{ $building->name }}" required><input name="description" class="form-control form-control-sm" value="{{ $building->description }}">
<select name="is_active" class="form-select form-select-sm"><option value="1" @selected($building->is_active)>Active</option><option value="0" @selected(!$building->is_active)>Inactive</option></select><button class="btn btn-sm btn-outline-primary">Save</button></form></td>
<td><form method="POST" action="{{ route('buildings.destroy',$building) }}" onsubmit="return confirm('Delete this building? Buildings with stalls cannot be deleted.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted">No buildings configured.</td></tr>@endforelse
</tbody></table></div></div></div>
</div>
@endsection
