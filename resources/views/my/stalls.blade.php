@extends('layouts.app')
@section('title','Available Stalls')
@section('content')
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Stall</th><th>Location</th><th>Size</th><th>Rate/mo</th><th>Status</th><th style="min-width:320px">Apply</th></tr></thead><tbody>
@foreach($stalls as $s)
<tr><td>{{ $s->stall_number }}</td><td>{{ $s->location }}</td><td>{{ $s->size_sqm }} m²</td><td>₱{{ number_format($s->monthly_rate,2) }}</td>
<td><span class="badge bg-{{ $s->status==='available'?'success':'secondary' }}">{{ ucfirst($s->status) }}</span></td>
<td>@if($s->status!=='available') <span class="text-muted small">Occupied</span>
@elseif(in_array($s->id,$pendingIds)) <span class="badge bg-warning">Application pending</span>
@else <form method="POST" action="{{ route('my.apply',$s) }}" class="d-flex gap-1">@csrf
  <input name="purpose" class="form-control form-control-sm" placeholder="Type of business" required>
  <button class="btn btn-sm btn-primary">Apply</button></form>@endif</td></tr>
@endforeach
</tbody></table></div></div>
@endsection
