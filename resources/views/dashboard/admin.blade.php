@extends('layouts.app')
@section('title','Admin Dashboard')
@section('content')
<div class="row g-3 mb-4">
  @foreach($stats as $label => $n)
  <div class="col-6 col-md-4 col-xl-2"><div class="card shadow-sm text-center"><div class="card-body">
    <div class="fs-2 fw-bold">{{ $n }}</div><div class="text-muted small">{{ $label }}</div></div></div></div>
  @endforeach
</div>
<div class="card shadow-sm"><div class="card-header bg-white fw-semibold">Recent Applications</div>
<div class="table-responsive"><table class="table mb-0 align-middle">
  <thead><tr><th>Applicant</th><th>Stall</th><th>Purpose</th><th>Status</th><th>Date</th></tr></thead>
  <tbody>@forelse($recent as $a)
    <tr><td>{{ $a->registrant->name }} <small class="text-muted">({{ $a->registrant->control_number }})</small></td>
    <td>{{ $a->stall->stall_number }}</td><td>{{ $a->purpose }}</td>
    <td><span class="badge bg-{{ ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$a->status] }}">{{ ucfirst($a->status) }}</span></td>
    <td>{{ $a->created_at->format('M d, Y') }}</td></tr>
  @empty <tr><td colspan="5" class="text-center text-muted">No applications yet.</td></tr>@endforelse</tbody>
</table></div></div>
@endsection
