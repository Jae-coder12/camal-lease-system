@extends('layouts.app')
@section('title','Stall Applications')
@section('content')
<div class="mb-3 d-flex gap-2">
  <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-secondary">All</a>
  @foreach(['pending','approved','rejected'] as $s)<a href="?status={{ $s }}" class="btn btn-sm btn-outline-secondary">{{ ucfirst($s) }}</a>@endforeach
</div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Applicant</th><th>Stall</th><th>Purpose</th><th>Status</th><th>Date</th><th style="min-width:300px">Action</th></tr></thead><tbody>
@forelse($apps as $a)
<tr><td>{{ $a->registrant->name }}<br><small class="text-muted">{{ $a->registrant->control_number }}</small></td>
<td>{{ $a->stall->stall_number }}</td><td>{{ $a->purpose }}</td>
<td><span class="badge bg-{{ ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$a->status] }}">{{ ucfirst($a->status) }}</span>
  @if($a->remarks)<br><small class="text-muted">{{ $a->remarks }}</small>@endif</td>
<td>{{ $a->created_at->format('M d, Y') }}</td>
<td>@if($a->status==='pending')
  <form method="POST" class="d-flex gap-1">@csrf
    <input name="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)">
    <button formaction="{{ route('applications.approve',$a) }}" class="btn btn-sm btn-success">Approve</button>
    <button formaction="{{ route('applications.reject',$a) }}" class="btn btn-sm btn-danger">Reject</button>
  </form>@endif</td></tr>
@empty <tr><td colspan="6" class="text-center text-muted">No applications.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $apps->links('pagination::bootstrap-5') }}</div>
@endsection
