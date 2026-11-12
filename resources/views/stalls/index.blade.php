@extends('layouts.app')
@section('title','Stall Management')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <form class="d-flex gap-2"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Stall number"><select name="building_id" class="form-select"><option value="">All buildings</option>@foreach($buildings as $building)<option value="{{ $building->id }}" @selected(request('building_id')==$building->id)>{{ $building->code }}</option>@endforeach</select><button class="btn btn-outline-secondary">Filter</button></form>
  <a href="{{ route('stalls.create') }}" class="btn btn-primary">+ Add Stall</a>
</div>
<div class="card shadow-sm"><div class="table-responsive"><table id="occupants" class="table mb-0 align-middle">
<thead><tr><th>Stall</th><th>Building</th><th>Occupant</th><th>Rate</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($stalls as $s)
<tr><td class="fw-semibold">{{ $s->stall_number }} @if($s->shared_group)<span class="badge text-bg-info">{{ $s->shared_group }}</span>@endif</td><td>{{ $s->building?->code }} · {{ $s->location }}</td><td>{{ $s->occupant_name ?? '—' }}</td>
<td>₱{{ number_format((float)($s->rental_rate ?? $s->monthly_rate),2) }}/{{ $s->payment_frequency==='daily'?'day':'month' }}</td>
<td><span class="badge bg-{{ $s->needs_verification||$s->status==='inactive'?'secondary':($s->status==='available'?'success':'danger') }}">{{ $s->needs_verification?'Verify':ucfirst($s->status) }}</span>
@if($s->applications->isNotEmpty()) <span class="badge text-bg-warning">Pending</span>@endif
@if($s->source_notes)<div class="small text-muted">{{ $s->source_notes }}</div>@endif</td>
<td class="text-end text-nowrap"><a href="{{ route('stalls.edit',$s) }}" class="btn btn-sm btn-outline-primary">Edit</a>
<form method="POST" action="{{ route('stalls.destroy',$s) }}" class="d-inline" onsubmit="return confirm('Delete this stall only if it has no history?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
@empty <tr><td colspan="6" class="text-center text-muted">No stalls match this filter.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $stalls->links('pagination::bootstrap-5') }}</div>
@endsection
