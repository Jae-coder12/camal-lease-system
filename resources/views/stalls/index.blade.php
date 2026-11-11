@extends('layouts.app')
@section('title','Stalls')
@section('content')
<div class="text-end mb-3"><a href="{{ route('stalls.create') }}" class="btn btn-primary">+ Add Stall</a></div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Stall</th><th>Location</th><th>Size</th><th>Rate/mo</th><th>Status</th><th>Current Owner</th><th></th></tr></thead><tbody>
@foreach($stalls as $s)
<tr><td>{{ $s->stall_number }}</td><td>{{ $s->location }}</td><td>{{ $s->size_sqm }} m²</td><td>₱{{ number_format($s->monthly_rate,2) }}</td>
<td><span class="badge bg-{{ $s->status==='available'?'success':'secondary' }}">{{ ucfirst($s->status) }}</span></td>
<td>{{ $s->owner ? $s->owner->name.' ('.$s->owner->control_number.')' : '—' }}</td>
<td class="text-end text-nowrap"><a href="{{ route('stalls.edit',$s) }}" class="btn btn-sm btn-outline-primary">Edit</a>
<form method="POST" action="{{ route('stalls.destroy',$s) }}" class="d-inline" onsubmit="return confirm('Delete this stall?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
@endforeach
</tbody></table></div></div>
<div class="mt-3">{{ $stalls->links('pagination::bootstrap-5') }}</div>
@endsection
