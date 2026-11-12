@extends('layouts.app')
@section('title','My Leases & Documents')
@section('content')
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Contract #</th><th>Stall</th><th>Period</th><th>Rental</th><th>Status</th><th>Payment history</th><th></th></tr></thead><tbody>
@forelse($leases as $l)
<tr><td>{{ $l->contract_number }}</td><td>{{ $l->stall->building?->code }} / {{ $l->stall->stall_number }}</td>
<td>{{ $l->start_date->format('M d, Y') }} – {{ $l->end_date?->format('M d, Y') }}</td><td>₱{{ number_format((float)($l->rental_amount ?? $l->monthly_rate),2) }}/{{ $l->payment_frequency==='daily'?'day':'month' }} @if($l->outstanding_balance!==null)<div class="small text-danger">Outstanding ₱{{ number_format($l->outstanding_balance,2) }}</div>@endif</td>
<td><span class="badge bg-{{ $l->monitoring_status==='Active'?'success':($l->monitoring_status==='Expiring Soon'?'warning':($l->monitoring_status==='Expired'?'danger':'secondary')) }}">{{ $l->monitoring_status }}</span></td>
<td>@forelse($l->payments as $payment)@if($payment->status==='paid')<a class="d-block" href="{{ route('payments.receipt',$payment) }}">{{ $payment->reference_number }} · ₱{{ number_format($payment->amount,2) }}</a>@else<span class="d-block small text-muted">{{ $payment->reference_number }} · {{ str($payment->status)->title() }}</span>@endif @empty<span class="text-muted small">No payments recorded.</span>@endforelse</td>
<td class="text-end"><a href="{{ route('leases.document',$l) }}" target="_blank" class="btn btn-sm btn-outline-primary">Print</a>
<a href="{{ route('leases.download',$l) }}" class="btn btn-sm btn-outline-secondary">Download PDF</a></td></tr>
@empty <tr><td colspan="7" class="text-center text-muted">No leases yet.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
