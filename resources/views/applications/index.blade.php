@extends('layouts.app')
@section('title','Stall Applications')
@section('content')
<form class="row g-2 mb-3"><div class="col-lg-3"><input name="control_number" value="{{ request('control_number') }}" class="form-control" placeholder="Control number"></div><div class="col-lg-3"><select name="building_id" class="form-select"><option value="">All buildings</option>@foreach(\App\Models\Building::orderBy('code')->get() as $building)<option value="{{ $building->id }}" @selected(request('building_id')==$building->id)>{{ $building->name }}</option>@endforeach</select></div><div class="col-lg-2"><input name="stall_number" value="{{ request('stall_number') }}" class="form-control" placeholder="Stall number"></div><div class="col-lg-2"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(['pending','under_review','approved','rejected','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select></div><div class="col-lg-2"><button class="btn btn-outline-secondary w-100">Filter</button></div></form>
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Applicant / control no.</th><th>Building / stall</th><th>Purpose</th><th>Status / reviewer</th><th>Application date</th><th style="min-width:310px">Transaction actions</th></tr></thead><tbody>
@forelse($apps as $a)<tr><td><a href="{{ route('registrants.show',$a->registrant) }}">{{ $a->registrant->name }}</a><br><small class="text-muted">{{ $a->registrant->control_number }}</small></td>
<td>{{ $a->stall->building?->code }} / {{ $a->stall->stall_number }}</td><td>{{ $a->purpose }} @if($a->supporting_document)<div class="small"><a href="{{ route('applications.document',$a) }}">Download supporting document</a></div>@endif</td>
<td><span class="badge bg-{{ ['pending'=>'warning','under_review'=>'info','approved'=>'success','rejected'=>'danger','completed'=>'primary','cancelled'=>'secondary'][$a->status] ?? 'secondary' }}">{{ str($a->status)->replace('_',' ')->title() }}</span>
@if($a->reviewer)<br><small class="text-muted">Action by {{ $a->reviewer->name }}</small>@endif
@if($a->remarks)<br><small class="text-muted">{{ $a->remarks }}</small>@endif</td><td>{{ $a->created_at->format('M d, Y') }}</td>
<td>
<details class="mb-2"><summary class="btn btn-sm btn-outline-secondary">Applicant details &amp; history</summary><div class="card card-body mt-1 small" style="min-width:300px">
<strong>{{ $a->registrant->name }}</strong> · {{ $a->registrant->control_number }}<br>
Civil status: {{ $a->registrant->status }} · Citizenship: {{ $a->registrant->citizenship }}<br>
Birth date: {{ $a->registrant->birthdate?->format('M d, Y') }} · Age: {{ $a->registrant->age ?? '—' }}<br>
Address: {{ $a->registrant->address }}<br>
Contact: {{ $a->registrant->contact_number }} · Email: {{ $a->registrant->user?->email ?? '—' }}<br>
@if($a->lease)<hr><strong>Generated contract:</strong> {{ $a->lease->contract_number }}<br><a href="{{ route('leases.document',$a->lease) }}">Preview</a> · <a href="{{ route('leases.download',$a->lease) }}">Download PDF</a>@endif
<hr><strong>Transaction history</strong>
@forelse($a->statusHistories as $history)<div>{{ $history->created_at->format('M d, Y H:i') }} — {{ str($history->to_status)->replace('_',' ')->title() }} by {{ $history->actor?->name ?? 'System/applicant' }}@if($history->remarks): {{ $history->remarks }}@endif</div>@empty<div>No history recorded.</div>@endforelse
</div></details>
@if(in_array($a->status,['pending','under_review'],true))
<div class="d-flex flex-column gap-2">
@if($a->status==='pending')<form method="POST" action="{{ route('applications.review',$a) }}">@csrf<button class="btn btn-sm btn-outline-primary w-100">Start review</button></form>@endif
<form method="POST" action="{{ route('applications.approve',$a) }}" class="d-flex gap-1" onsubmit="return confirm('Approve this application and generate a lease contract?')">@csrf<input name="remarks" class="form-control form-control-sm" placeholder="Approval remarks"><button class="btn btn-sm btn-success">Approve &amp; create lease</button></form>
<form method="POST" action="{{ route('applications.reject',$a) }}" class="d-flex gap-1" onsubmit="return confirm('Reject this application? This decision will be recorded in the transaction history.')">@csrf<input name="remarks" class="form-control form-control-sm" placeholder="Reason for rejection" required><button class="btn btn-sm btn-danger">Reject</button></form>
</div>
@elseif($a->status==='approved')<form method="POST" action="{{ route('applications.complete',$a) }}">@csrf<button class="btn btn-sm btn-primary">Mark completed</button></form>@endif
</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted">No applications match this filter.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-3">{{ $apps->links('pagination::bootstrap-5') }}</div>
@endsection
