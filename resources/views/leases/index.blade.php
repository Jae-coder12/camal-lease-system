@extends('layouts.app')
@section('title','Leases & Documents')
@section('content')
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Contract #</th><th>Stall</th><th>Lessee</th><th>Period</th><th>Rate</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($leases as $l)
<tr><td>{{ $l->contract_number }}</td><td>{{ $l->stall->stall_number }}</td><td>{{ $l->registrant->name }}</td>
<td>{{ $l->start_date->format('M d, Y') }} – {{ $l->end_date?->format('M d, Y') }}</td><td>₱{{ number_format($l->monthly_rate,2) }}</td>
<td><span class="badge bg-{{ $l->status==='active'?'success':'secondary' }}">{{ ucfirst($l->status) }}</span></td>
<td class="text-end text-nowrap">
  <a href="{{ route('leases.document',$l) }}" target="_blank" class="btn btn-sm btn-outline-primary">Print</a>
  <a href="{{ route('leases.download',$l) }}" class="btn btn-sm btn-outline-secondary">PDF</a>
  @if($l->status==='active')<form method="POST" action="{{ route('leases.end',$l) }}" class="d-inline" onsubmit="return confirm('End this lease and free the stall?')">@csrf<button class="btn btn-sm btn-outline-danger">End</button></form>@endif
</td></tr>
@empty <tr><td colspan="7" class="text-center text-muted">No leases yet.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $leases->links('pagination::bootstrap-5') }}</div>
@endsection
