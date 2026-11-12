@extends('layouts.app')
@section('title','Lease Monitoring')
@section('content')
<form class="row g-2 mb-3"><div class="col-md-4"><select name="building_id" class="form-select"><option value="">All buildings</option>@foreach($buildings as $building)<option value="{{ $building->id }}" @selected(request('building_id')==$building->id)>{{ $building->name }}</option>@endforeach</select></div><div class="col-md-4"><select name="status" class="form-select"><option value="">All statuses</option><option value="active" @selected(request('status')==='active')>Active</option><option value="upcoming" @selected(request('status')==='upcoming')>Upcoming</option><option value="expiring" @selected(request('status')==='expiring')>Expiring soon</option><option value="expired" @selected(request('status')==='expired')>Expired</option><option value="terminated" @selected(request('status')==='terminated')>Terminated</option></select></div><div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div><div class="col-md-2"><a href="{{ route('leases.index') }}" class="btn btn-link">Clear</a></div></form>
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Contract</th><th>Occupant / control no.</th><th>Building / stall</th><th>Rental</th><th>Start</th><th>Expiration</th><th>Remaining</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($leases as $l)
@php $days=$l->end_date ? today()->diffInDays($l->end_date,false) : null; $monitor=$l->monitoring_status; @endphp
<tr><td>{{ $l->contract_number }}</td><td>{{ $l->registrant->name }}<div class="small text-muted">{{ $l->registrant->control_number }}</div></td><td>{{ $l->stall->building?->code }} / {{ $l->stall->stall_number }}</td>
<td>₱{{ number_format((float)($l->rental_amount ?? $l->monthly_rate),2) }}/{{ $l->payment_frequency==='daily'?'day':'month' }}</td><td>{{ $l->start_date->format('M d, Y') }}</td><td>{{ $l->end_date?->format('M d, Y') ?? '—' }}</td>
<td>@if($days!==null){{ $days<0?'Expired':$days.' days' }}@else — @endif</td><td><span class="badge bg-{{ $monitor==='Active'?'success':($monitor==='Expiring Soon'?'warning':($monitor==='Expired'?'danger':'secondary')) }}">{{ $monitor }}</span></td>
<td class="text-end text-nowrap"><details class="d-inline-block text-start"><summary class="btn btn-sm btn-outline-secondary">Edit contract</summary><form method="POST" action="{{ route('leases.update',$l) }}" class="card card-body mt-1 text-start" style="min-width:280px">@csrf @method('PUT')
<label class="form-label small">Start date</label><input type="date" name="start_date" class="form-control form-control-sm mb-2" value="{{ $l->start_date->format('Y-m-d') }}" required>
<label class="form-label small">End date</label><input type="date" name="end_date" class="form-control form-control-sm mb-2" value="{{ $l->end_date?->format('Y-m-d') }}">
<label class="form-label small">Rental amount</label><input type="number" step="0.01" min="0" name="rental_amount" class="form-control form-control-sm mb-2" value="{{ $l->rental_amount ?? $l->monthly_rate }}" required>
<label class="form-label small">Frequency</label><select name="payment_frequency" class="form-select form-select-sm mb-2"><option value="monthly" @selected($l->payment_frequency==='monthly')>Monthly</option><option value="daily" @selected($l->payment_frequency==='daily')>Daily</option></select>
<label class="form-label small">Contract terms</label><textarea name="contract_terms" class="form-control form-control-sm mb-2" rows="5">{{ $l->contract_terms }}</textarea><button class="btn btn-sm btn-primary">Save &amp; audit</button></form></details>
<a href="{{ route('leases.document',$l) }}" class="btn btn-sm btn-outline-primary">Lease</a><a href="{{ route('leases.download',$l) }}" class="btn btn-sm btn-outline-secondary">PDF</a>
@if(in_array($l->status,['active','expired'],true) && !$l->renewal)
@php $renewStart = $l->end_date && $l->end_date->gte(today()) ? $l->end_date->copy()->addDay() : today(); @endphp
<details class="d-inline-block text-start"><summary class="btn btn-sm btn-outline-success">Renew</summary><form method="POST" action="{{ route('leases.renew',$l) }}" class="card card-body mt-1 text-start" style="min-width:280px">@csrf
<label class="form-label small">New start date</label><input type="date" name="start_date" class="form-control form-control-sm mb-2" min="{{ today()->toDateString() }}" value="{{ $renewStart->toDateString() }}" required>
<label class="form-label small">New expiration date</label><input type="date" name="end_date" class="form-control form-control-sm mb-2" value="{{ $renewStart->copy()->addYear()->toDateString() }}" required>
<label class="form-label small">Rental amount</label><input type="number" name="rental_amount" min="0" step="0.01" class="form-control form-control-sm mb-2" value="{{ $l->rental_amount ?? $l->monthly_rate }}" required>
<label class="form-label small">Frequency</label><select name="payment_frequency" class="form-select form-select-sm mb-2"><option value="monthly" @selected($l->payment_frequency==='monthly')>Monthly</option><option value="daily" @selected($l->payment_frequency==='daily')>Daily</option></select>
<label class="form-label small">Contract terms</label><textarea name="contract_terms" class="form-control form-control-sm mb-2" rows="4">{{ $l->contract_terms }}</textarea><button class="btn btn-sm btn-success">Create renewed contract</button></form></details>
@endif
@if($l->status==='active')<form method="POST" action="{{ route('leases.end',$l) }}" class="d-inline" onsubmit="return confirm('Terminate this lease and release this occupant space?')">@csrf<button class="btn btn-sm btn-outline-danger">Terminate</button></form>@endif</td></tr>
@empty <tr><td colspan="9" class="text-center text-muted">No leases match this filter.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-3">{{ $leases->links('pagination::bootstrap-5') }}</div>
@endsection
