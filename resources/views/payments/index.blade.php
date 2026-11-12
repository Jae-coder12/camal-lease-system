@extends('layouts.app')
@section('title','Rental Payments')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Record a payment</div>
    <div class="card-body">
        <form method="POST" action="{{ route('payments.store') }}" class="row g-3" onsubmit="return confirm('Record this payment and generate its receipt?')">@csrf
            <div class="col-lg-5"><label class="form-label">Active lease</label><select name="lease_id" class="form-select" required>
                    <option value="">Select lease</option>@foreach($leases as $lease)<option value="{{ $lease->id }}" @selected(old('lease_id')==$lease->id)>{{ $lease->contract_number }} · {{ $lease->registrant->name }} · {{ $lease->stall->building?->code }}-{{ $lease->stall->stall_number }}</option>@endforeach
                </select></div>
            <div class="col-md-3 col-lg-2"><label class="form-label">Amount (₱)</label><input name="amount" type="number" step="0.01" min="0.01" class="form-control" value="{{ old('amount') }}" required></div>
            <div class="col-md-3 col-lg-2"><label class="form-label">Payment date</label><input name="payment_date" type="date" max="{{ today()->toDateString() }}" class="form-control" value="{{ old('payment_date',today()->toDateString()) }}" required></div>
            <div class="col-md-3 col-lg-3"><label class="form-label">Period covered</label><input name="period_covered" class="form-control" maxlength="80" placeholder="e.g. November 2026"></div>
            <div class="col-md-3 col-lg-3"><label class="form-label">Method</label><select name="method" class="form-select">
                    <option value="">Select</option>@foreach(['cash'=>'Cash','bank_transfer'=>'Bank transfer','check'=>'Check','other'=>'Other'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select></div>
            <div class="col-md-6 col-lg-6"><label class="form-label">Remarks</label><input name="remarks" class="form-control" maxlength="1000"></div>
            <div class="col-12"><button class="btn btn-primary">Record payment &amp; create receipt</button></div>
        </form>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Transaction review and payment history</div>
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label small" for="payment-search">Applicant or reference</label><input id="payment-search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, control number, reference"></div>
            <div class="col-sm-6 col-md-2"><label class="form-label small" for="payment-status">Status</label><select id="payment-status" name="status" class="form-select"><option value="">All statuses</option>@foreach(['pending'=>'Pending review','paid'=>'Paid','rejected'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-md-2"><label class="form-label small" for="payment-method">Method</label><select id="payment-method" name="method" class="form-select"><option value="">All methods</option>@foreach(['cash'=>'Cash','bank_transfer'=>'Bank transfer','check'=>'Check','other'=>'Other'] as $value=>$label)<option value="{{ $value }}" @selected(request('method')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-md-2"><label class="form-label small" for="payment-date-from">From</label><input id="payment-date-from" name="date_from" type="date" value="{{ request('date_from') }}" class="form-control"></div>
            <div class="col-sm-6 col-md-2"><label class="form-label small" for="payment-date-to">To</label><input id="payment-date-to" name="date_to" type="date" value="{{ request('date_to') }}" class="form-control"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-sm btn-outline-primary">Apply filters</button>@if(request()->hasAny(['q','status','method','date_from','date_to']))<a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>@endif</div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Applicant / stall</th>
                    <th>Amount</th>
                    <th>Period</th>
                    <th>Payment date</th>
                    <th>Status</th>
                    <th>Recorded by</th>
                    <th>Review / receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)<tr>
                    <td>{{ $payment->reference_number }}</td>
                    <td>{{ $payment->lease->registrant->name }}
                        <div class="small text-muted">{{ $payment->lease->stall->building?->code }} · {{ $payment->lease->stall->stall_number }}</div>
                    </td>
                    <td>₱{{ number_format($payment->amount,2) }}</td>
                    <td>{{ $payment->period_covered ?? '—' }}</td>
                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                    <td><span class="badge rounded-pill bg-{{ $payment->status==='paid'?'success':($payment->status==='pending'?'warning':($payment->status==='rejected'?'danger':'secondary')) }}">{{ str($payment->status)->title() }}</span>
                        @if($payment->status==='rejected' && $payment->remarks)<div class="small text-muted mt-1">{{ $payment->remarks }}</div>@endif
                    </td>
                    <td>{{ $payment->recorder->name }}</td>
                    <td class="text-nowrap">
                        @if($payment->status==='pending')
                            <form method="POST" action="{{ route('payments.review',$payment) }}" class="d-inline" onsubmit="return confirm('Approve this payment and mark it as paid?')">@csrf<input type="hidden" name="decision" value="approve"><button class="btn btn-sm btn-success">Approve</button></form>
                            <form method="POST" action="{{ route('payments.review',$payment) }}" class="d-flex gap-1 mt-1" onsubmit="return confirm('Reject this payment? The reason will be shared with the applicant.')">@csrf<input type="hidden" name="decision" value="reject"><input name="remarks" class="form-control form-control-sm" placeholder="Rejection reason" aria-label="Rejection reason" required><button class="btn btn-sm btn-outline-danger">Reject</button></form>
                        @elseif($payment->status==='paid')
                            <a href="{{ route('payments.receipt',$payment) }}" class="btn btn-sm btn-outline-primary">Receipt</a>
                        @else<span class="small text-muted">No receipt</span>@endif
                    </td>
                </tr>
                @empty<tr>
                    <td colspan="8" class="text-center text-muted">No payment transactions match these filters.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white fw-semibold">Monthly lease balances</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Applicant / stall</th>
                    <th>Monthly charge</th>
                    <th>Paid to date</th>
                    <th>Outstanding balance*</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leases as $lease)<tr>
                    <td>{{ $lease->registrant->name }} · {{ $lease->stall->building?->code }}-{{ $lease->stall->stall_number }}</td>
                    <td>₱{{ number_format((float)($lease->rental_amount ?? $lease->monthly_rate),2) }}</td>
                    <td>₱{{ number_format((float)$lease->payments()->where('status','paid')->sum('amount'),2) }}</td>
                    <td>@if($lease->outstanding_balance!==null)₱{{ number_format($lease->outstanding_balance,2) }}@else Usage-based (not accrued)@endif</td>
                </tr>
                @empty<tr>
                    <td colspan="4" class="text-center text-muted">No active leases.</td>@endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer small text-muted">*Monthly charges are estimated by calendar month from lease start through today. Daily-cash tickets are usage-based and are not accrued automatically.</div>
</div>
@endsection