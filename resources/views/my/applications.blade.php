@extends('layouts.app')
@section('title','My Applications')
@section('content')
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Building / stall</th><th>Purpose</th><th>Status</th><th>Remarks</th><th>Date</th><th></th></tr></thead><tbody>
@forelse($apps as $a)
<tr><td>{{ $a->stall->building?->name }} / {{ $a->stall->stall_number }}</td><td>{{ $a->purpose }}</td>
<td><span class="badge bg-{{ ['pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'][$a->status] ?? 'secondary' }}">{{ ucfirst($a->status) }}</span></td>
<td>{{ $a->remarks }}</td><td>{{ $a->created_at->format('M d, Y') }}</td><td>@if($a->status==='pending')<form method="POST" action="{{ route('my.applications.cancel',$a) }}" onsubmit="return confirm('Cancel this application?')">@csrf<button class="btn btn-sm btn-outline-danger">Cancel</button></form>@endif</td></tr>
@empty <tr><td colspan="6" class="text-center text-muted">No applications yet.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
