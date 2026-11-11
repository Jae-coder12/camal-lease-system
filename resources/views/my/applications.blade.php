@extends('layouts.app')
@section('title','My Applications')
@section('content')
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Stall</th><th>Purpose</th><th>Status</th><th>Remarks</th><th>Date</th></tr></thead><tbody>
@forelse($apps as $a)
<tr><td>{{ $a->stall->stall_number }}</td><td>{{ $a->purpose }}</td>
<td><span class="badge bg-{{ ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$a->status] }}">{{ ucfirst($a->status) }}</span></td>
<td>{{ $a->remarks }}</td><td>{{ $a->created_at->format('M d, Y') }}</td></tr>
@empty <tr><td colspan="5" class="text-center text-muted">No applications yet.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
