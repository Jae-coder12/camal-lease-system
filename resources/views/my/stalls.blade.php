@extends('layouts.app')
@section('title','Browse Market Stalls')
@section('content')
<p class="text-secondary">Your control number: <strong>{{ $reg?->control_number ?? 'Profile unavailable' }}</strong>. A submitted application reserves one available occupant space until staff review it.</p>
<div class="row g-3">
@forelse($stalls as $s)
@php
  $remaining = $s->remainingCapacity();
  $status = $s->needs_verification || $s->status==='inactive' ? 'Verify' : ($remaining > 0 ? ($s->applications->count() ? 'Pending' : 'Available') : 'Occupied');
@endphp
<div class="col-md-6 col-xl-4"><div class="card shadow-sm h-100"><div class="card-body d-flex flex-column">
<div class="d-flex justify-content-between"><div><div class="text-muted small">{{ $s->building?->name ?? $s->location }}</div><h5 class="mb-1">Stall {{ $s->stall_number }}</h5></div>
<span class="badge bg-{{ $status==='Available'?'success':($status==='Pending'?'warning':($status==='Occupied'?'danger':'secondary')) }} align-self-start">{{ $status }}</span></div>
<div class="small text-secondary mb-2">{{ $s->stall_type ?? 'Market stall' }} · ₱{{ number_format((float)($s->rental_rate ?? $s->monthly_rate),2) }}/{{ $s->payment_frequency==='daily'?'day':'month' }}</div>
@if($s->occupant_name)<div class="small mb-2"><strong>Recorded occupant:</strong> {{ $s->occupant_name }}</div>@endif
@if($s->needs_verification)<div class="alert alert-secondary py-2 small">{{ $s->source_notes ?? 'This source record needs administrator verification.' }}</div>@endif
@if($remaining > 0 && !$s->needs_verification && $reg)
<form method="POST" action="{{ route('my.apply',$s) }}" enctype="multipart/form-data" class="mt-auto">@csrf
<label class="form-label small">Business purpose</label><input name="purpose" class="form-control form-control-sm mb-2" maxlength="150" required placeholder="e.g. vegetable retail">
<details class="small mb-2"><summary>Attach supporting document (optional)</summary><input type="file" name="supporting_document" accept=".pdf,.jpg,.jpeg,.png" class="form-control form-control-sm mt-2"><div class="form-text">PDF/JPG/PNG, up to 5 MB.</div></details>
<button class="btn btn-primary btn-sm w-100">Apply for this stall</button></form>
@else
<div class="mt-auto small text-muted">{{ $status==='Pending' ? 'Application pending review.' : ($status==='Available' ? 'Sign in as an applicant to apply.' : 'Not currently accepting applications.') }}</div>
@endif
</div></div></div>
@empty <div class="col-12"><div class="alert alert-info">No stalls are available to browse.</div></div>@endforelse
</div>
<div class="mt-3">{{ $stalls->links('pagination::bootstrap-5') }}</div>
@endsection
