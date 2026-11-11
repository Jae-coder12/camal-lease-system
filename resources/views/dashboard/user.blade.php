@extends('layouts.app')
@section('title','My Dashboard')
@section('content')
@if($reg)
<div class="row g-3 mb-4">
  <div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
    <h6 class="text-muted">My Profile</h6>
    <p class="mb-1"><strong>Control No:</strong> {{ $reg->control_number }}</p>
    <p class="mb-1"><strong>Name:</strong> {{ $reg->name }}</p>
    <p class="mb-1"><strong>Status:</strong> {{ $reg->status }} &nbsp; <strong>Citizenship:</strong> {{ $reg->citizenship }}</p>
    <p class="mb-1"><strong>Birthdate:</strong> {{ $reg->birthdate->format('M d, Y') }} ({{ $reg->age }} yrs)</p>
    <p class="mb-1"><strong>Address:</strong> {{ $reg->address }}</p>
    <p class="mb-0"><strong>Contact:</strong> {{ $reg->contact_number }}</p>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm text-center"><div class="card-body"><div class="fs-2 fw-bold">{{ $stalls->count() }}</div><div class="text-muted small">Stalls I own</div></div></div></div>
  <div class="col-md-3"><div class="card shadow-sm text-center"><div class="card-body"><div class="fs-2 fw-bold">{{ $pending }}</div><div class="text-muted small">Pending applications</div></div></div></div>
</div>
<div class="card shadow-sm"><div class="card-header bg-white fw-semibold">My Stalls</div>
<ul class="list-group list-group-flush">
  @forelse($stalls as $s)<li class="list-group-item">{{ $s->stall_number }} — {{ $s->location }} (₱{{ number_format($s->monthly_rate,2) }}/mo)</li>
  @empty <li class="list-group-item text-muted">You don't own a stall yet. <a href="{{ route('my.stalls') }}">Browse available stalls</a>.</li>@endforelse
</ul></div>
@else <div class="alert alert-warning">No registration profile attached to this account.</div>@endif
@endsection
