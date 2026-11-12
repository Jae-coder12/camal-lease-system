@extends('layouts.app')
@section('title','Registrant Profile')
@section('content')
<div class="d-flex justify-content-between mb-3"><a href="{{ route('registrants.index') }}" class="btn btn-outline-secondary">Back</a><a href="{{ route('registrants.edit',$registrant) }}" class="btn btn-primary">Edit profile</a></div>
<div class="card shadow-sm mb-3"><div class="card-header bg-white fw-semibold">Applicant details</div><div class="card-body"><div class="row g-3">
<div class="col-md-4"><div class="small text-muted">Control number</div><strong>{{ $registrant->control_number }}</strong></div><div class="col-md-4"><div class="small text-muted">Full name</div><strong>{{ $registrant->name }}</strong></div><div class="col-md-4"><div class="small text-muted">Email</div>{{ $registrant->user?->email ?? 'No login account' }}</div>
<div class="col-md-3"><div class="small text-muted">Civil status</div>{{ $registrant->status }}</div><div class="col-md-3"><div class="small text-muted">Citizenship</div>{{ $registrant->citizenship }}</div><div class="col-md-3"><div class="small text-muted">Date of birth / age</div>{{ $registrant->birthdate->format('M d, Y') }} / {{ $registrant->age }}</div><div class="col-md-3"><div class="small text-muted">Contact</div>{{ $registrant->contact_number }}</div>
<div class="col-12"><div class="small text-muted">Residential address</div>{{ $registrant->address }}</div></div></div></div>
<div class="row g-3"><div class="col-lg-6"><div class="card shadow-sm h-100"><div class="card-header bg-white fw-semibold">Application history</div><div class="list-group list-group-flush">
@forelse($registrant->applications as $application)<div class="list-group-item"><div class="d-flex justify-content-between"><strong>{{ $application->stall->building?->code }} · {{ $application->stall->stall_number }}</strong><span>{{ ucfirst($application->status) }}</span></div><div>{{ $application->purpose }}</div><small class="text-muted">{{ $application->created_at->format('M d, Y') }} @if($application->remarks) · {{ $application->remarks }} @endif</small></div>
@empty<div class="list-group-item text-muted">No applications.</div>@endforelse
</div></div></div><div class="col-lg-6"><div class="card shadow-sm h-100"><div class="card-header bg-white fw-semibold">Lease and stall history</div><div class="list-group list-group-flush">
@forelse($registrant->leases as $lease)<div class="list-group-item"><div class="d-flex justify-content-between"><strong>{{ $lease->stall->building?->code }} · {{ $lease->stall->stall_number }} · {{ $lease->contract_number }}</strong><span>{{ $lease->monitoring_status }}</span></div><div>₱{{ number_format((float)($lease->rental_amount ?? $lease->monthly_rate),2) }}/{{ $lease->payment_frequency==='daily'?'day':'month' }}</div><small class="text-muted">{{ $lease->start_date->format('M d, Y') }} – {{ $lease->end_date?->format('M d, Y') ?? 'Current' }}</small></div>
@empty<div class="list-group-item text-muted">No leases.</div>@endforelse
</div></div></div></div>
@endsection
