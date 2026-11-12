@extends('layouts.app')
@section('title','Applicant Dashboard')
@section('content')
<style>
  .user-welcome{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.15rem;padding:1.2rem 1.35rem;border:1px solid #e1e9ed;border-radius:14px;background:linear-gradient(110deg,#fff 60%,#eef7f1)}
  .user-welcome h2{margin:0;color:#12324a;font-size:1.2rem;font-weight:750}
  .user-welcome p{margin:.35rem 0 0;color:#64748b;font-size:.88rem}
  .user-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem;margin-bottom:1.2rem}
  .user-stat{display:flex;align-items:center;gap:.8rem;min-height:84px;padding:.9rem 1rem;border:1px solid #e5ebef;border-radius:13px;background:#fff;box-shadow:0 4px 16px rgba(18,50,74,.045);color:inherit;text-decoration:none}
  .user-stat:hover{border-color:#b8d7c6;box-shadow:0 7px 20px rgba(18,50,74,.08)}
  .user-stat-icon{display:grid;flex:0 0 38px;width:38px;height:38px;place-items:center;border-radius:11px;background:#e7f3ed;color:#198754;font-size:1.05rem}
  .user-stat-icon.blue{background:#eaf2f8;color:#25658a}.user-stat-icon.amber{background:#fff4de;color:#9a6700}.user-stat-icon.red{background:#fdecec;color:#b42318}
  .user-stat-value{display:block;color:#12324a;font-size:1.35rem;font-weight:780;line-height:1.1}
  .user-stat-label{display:block;margin-top:.25rem;color:#64748b;font-size:.75rem;line-height:1.2}
  .user-panel{height:100%;border:1px solid #e4eaf0;border-radius:13px;background:#fff;box-shadow:0 5px 18px rgba(18,50,74,.045)}
  .user-panel-head{display:flex;align-items:center;justify-content:space-between;gap:.7rem;padding:.9rem 1rem;border-bottom:1px solid #edf1f4}
  .user-panel-head h3{margin:0;color:#12324a;font-size:.9rem;font-weight:750}
  .user-panel-body{padding:1rem}
  .user-table{margin:0;font-size:.83rem}
  .user-table td,.user-table th{padding:.68rem .8rem;vertical-align:middle}
  .user-notification{padding:.8rem 1rem;border-bottom:1px solid #edf1f4}
  .user-notification:last-child{border-bottom:0}
  .user-notification.unread{background:#f5faf7}
  .user-empty{padding:1.5rem 1rem;color:#718096;text-align:center}
  .user-empty i{display:block;margin-bottom:.45rem;color:#9aabba;font-size:1.5rem}
  .user-lease{padding:1rem;border:1px solid #e6ecf0;border-radius:11px;background:#fbfcfd}
  .user-lease+.user-lease{margin-top:.7rem}
  .user-steps{display:flex;align-items:flex-start;gap:.35rem;margin-top:.8rem}
  .user-step{position:relative;flex:1;color:#788898;font-size:.67rem;text-align:center}
  .user-step:before{display:block;width:12px;height:12px;margin:0 auto .35rem;border:2px solid #cbd5df;border-radius:50%;background:#fff;content:""}
  .user-step:not(:last-child):after{position:absolute;top:5px;left:calc(50% + 8px);width:calc(100% - 16px);height:2px;background:#dbe3e9;content:""}
  .user-step.done{color:#198754;font-weight:650}
  .user-step.done:before{border-color:#198754;background:#198754}
  .user-step.done:not(:last-child):after{background:#198754}
  .user-profile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.35rem 1rem;color:#53687b;font-size:.82rem}.user-profile-grid .col-span-2{grid-column:1/-1}
  @media(max-width:1000px){.user-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(max-width:600px){.user-welcome{align-items:flex-start;flex-direction:column;padding:1rem}.user-stats{gap:.55rem}.user-stat{min-height:76px;padding:.7rem}.user-stat-icon{flex-basis:34px;width:34px;height:34px}.user-stat-value{font-size:1.18rem}.user-profile-grid{grid-template-columns:1fr}}
  @media(max-width:350px){.user-stats{grid-template-columns:1fr}}
</style>

<div class="user-welcome">
  <div>
    <h2>Welcome back, {{ $reg?->name ?? auth()->user()->name }}</h2>
    <p>Track your market applications, stall assignments, lease documents, and rental payments.</p>
    @if($reg)<div class="small text-muted mt-2">Applicant control number: <strong class="text-dark">{{ $reg->control_number }}</strong></div>@endif
  </div>
  <a class="btn btn-success" href="{{ route('my.stalls') }}"><i class="bi bi-search me-1"></i>Explore available stalls</a>
</div>

<section class="user-stats" aria-label="Your market account summary">
  <a class="user-stat" href="{{ route('my.applications') }}"><span class="user-stat-icon blue"><i class="bi bi-inbox"></i></span><span><span class="user-stat-value">{{ $applicationCounts['total'] }}</span><span class="user-stat-label">My applications</span></span></a>
  <a class="user-stat" href="{{ route('my.applications') }}"><span class="user-stat-icon amber"><i class="bi bi-hourglass-split"></i></span><span><span class="user-stat-value">{{ $applicationCounts['pending'] }}</span><span class="user-stat-label">Pending / under review</span></span></a>
  <a class="user-stat" href="{{ route('my.applications') }}"><span class="user-stat-icon"><i class="bi bi-check-circle"></i></span><span><span class="user-stat-value">{{ $applicationCounts['approved'] }}</span><span class="user-stat-label">Approved applications</span></span></a>
  <a class="user-stat" href="{{ route('my.applications') }}"><span class="user-stat-icon red"><i class="bi bi-x-circle"></i></span><span><span class="user-stat-value">{{ $applicationCounts['rejected'] }}</span><span class="user-stat-label">Rejected applications</span></span></a>
  <a class="user-stat" href="{{ route('my.leases') }}"><span class="user-stat-icon"><i class="bi bi-shop"></i></span><span><span class="user-stat-value">{{ $stalls->count() }}</span><span class="user-stat-label">Current stall assignments</span></span></a>
  <a class="user-stat" href="{{ route('my.leases') }}"><span class="user-stat-icon blue"><i class="bi bi-file-earmark-check"></i></span><span><span class="user-stat-value">{{ $stalls->count() }}</span><span class="user-stat-label">Active leases</span></span></a>
  <a class="user-stat" href="{{ route('my.leases') }}"><span class="user-stat-icon amber"><i class="bi bi-clock-history"></i></span><span><span class="user-stat-value">{{ $paymentCounts['pending'] }}</span><span class="user-stat-label">Pending payments</span></span></a>
  <a class="user-stat" href="{{ route('my.leases') }}"><span class="user-stat-icon"><i class="bi bi-receipt"></i></span><span><span class="user-stat-value">{{ $paymentCounts['completed'] }}</span><span class="user-stat-label">Completed payments</span></span></a>
</section>

<section class="row g-3">
  <div class="col-xl-7">
    <div class="user-panel">
      <div class="user-panel-head"><h3>Application tracking</h3><a href="{{ route('my.applications') }}" class="small text-decoration-none">All applications</a></div>
      <div class="table-responsive">
        <table class="table user-table">
          <thead><tr><th>Market stall</th><th>Status &amp; progress</th><th>Latest update</th></tr></thead>
          <tbody>
          @forelse($applications->take(5) as $application)
            @php
              $progress = match($application->status) {
                'pending' => 1,
                'under_review' => 2,
                'rejected' => 3,
                'approved' => 5,
                'completed' => 6,
                default => 0,
              };
              $statusTone = ['pending'=>'warning','under_review'=>'info','approved'=>'success','rejected'=>'danger','completed'=>'primary','cancelled'=>'secondary'][$application->status] ?? 'secondary';
            @endphp
            <tr>
              <td><span class="fw-semibold">{{ $application->stall->building?->code }} / {{ $application->stall->stall_number }}</span><span class="d-block small text-muted">{{ $application->purpose }}</span><span class="d-block small text-muted">Application #{{ $application->id }} · Submitted {{ $application->created_at->format('M d, Y') }}</span></td>
              <td><span class="badge rounded-pill bg-{{ $statusTone }}">{{ str($application->status)->replace('_',' ')->title() }}</span>
                <div class="user-steps" aria-label="Application progress">
                  <span class="user-step done">Submitted</span><span class="user-step {{ $progress>=2?'done':'' }}">Review</span><span class="user-step {{ $progress>=3?'done':'' }}">Decision</span><span class="user-step {{ $progress>=4?'done':'' }}">Stall</span><span class="user-step {{ $progress>=5?'done':'' }}">Contract</span><span class="user-step {{ $progress>=6?'done':'' }}">Complete</span>
                </div>
              </td>
              <td><span class="small text-muted">{{ $application->updated_at->diffForHumans() }}</span>@if($application->remarks)<span class="d-block small mt-1">{{ $application->remarks }}</span>@else<span class="d-block small text-muted mt-1">No remarks posted</span>@endif</td>
            </tr>
          @empty<tr><td colspan="3"><div class="user-empty"><i class="bi bi-inbox"></i>No applications yet. Browse available stalls to submit an application.</div></td></tr>@endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="user-panel h-100">
      <div class="user-panel-head"><h3>My stall assignments &amp; leases</h3><a href="{{ route('my.leases') }}" class="small text-decoration-none">Lease history</a></div>
      <div class="user-panel-body">
        @forelse($stalls as $lease)
          <article class="user-lease">
            <div class="d-flex justify-content-between align-items-start gap-2"><div class="fw-semibold">{{ $lease->stall->building?->name }} · Stall {{ $lease->stall->stall_number }}</div><span class="badge rounded-pill bg-{{ $lease->monitoring_status==='Expired'?'danger':($lease->monitoring_status==='Expiring Soon'?'warning':'success') }}">{{ $lease->monitoring_status }}</span></div>
            <div class="small text-muted mt-1">{{ $lease->stall->location ?: 'Market stall' }} · ₱{{ number_format((float)($lease->rental_amount ?? $lease->monthly_rate),2) }}/{{ $lease->payment_frequency==='daily'?'day':'month' }}</div>
            <div class="small text-muted">Lease {{ $lease->start_date?->format('M d, Y') ?? '—' }} to {{ $lease->end_date?->format('M d, Y') ?? 'No end date' }}</div>
            <div class="d-flex flex-wrap gap-2 mt-2"><a href="{{ route('leases.document',$lease) }}" class="btn btn-sm btn-outline-primary">View contract</a><a href="{{ route('my.leases') }}" class="btn btn-sm btn-outline-secondary">Payments &amp; history</a></div>
          </article>
        @empty
          <div class="user-empty"><i class="bi bi-shop"></i>No current stall assignment. You can apply for an available stall after reviewing its details.<div class="mt-3"><a href="{{ route('my.stalls') }}" class="btn btn-sm btn-success">Browse available stalls</a></div></div>
        @endforelse
      </div>
    </div>
  </div>
</section>

<section class="row g-3 mt-0">
  <div class="col-xl-7">
    <div class="user-panel">
      <div class="user-panel-head"><h3>Recent payment history</h3><a href="{{ route('my.leases') }}" class="small text-decoration-none">All leases &amp; receipts</a></div>
      <div class="table-responsive"><table class="table user-table">
        <thead><tr><th>Payment date</th><th>Reference</th><th>Stall</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($payments as $payment)
          <tr><td class="text-nowrap">{{ $payment->payment_date?->format('M d, Y') }}</td><td>{{ $payment->reference_number }}</td><td class="text-nowrap">{{ $payment->lease->stall->building?->code }} / {{ $payment->lease->stall->stall_number }}</td><td class="text-nowrap">₱{{ number_format((float)$payment->amount,2) }}</td><td><span class="badge rounded-pill bg-{{ $payment->status==='paid'?'success':($payment->status==='pending'?'warning':($payment->status==='rejected'?'danger':'secondary')) }}">{{ str($payment->status)->title() }}</span></td><td>@if($payment->status==='paid')<a href="{{ route('payments.receipt',$payment) }}" class="btn btn-sm btn-outline-primary">Receipt</a>@else<span class="small text-muted">Not available</span>@endif</td></tr>
        @empty<tr><td colspan="6"><div class="user-empty">No rental payment records are available.</div></td></tr>@endforelse
        </tbody></table></div>
    </div>
  </div>
  <div class="col-xl-5">
    <div id="notifications" class="user-panel h-100">
      <div class="user-panel-head"><h3>Notifications</h3>@if($unreadNotifications)<span class="badge rounded-pill text-bg-success">{{ $unreadNotifications }} new</span>@endif</div>
      <div>
        @forelse($notifications as $notification)
          <div class="user-notification {{ $notification->read_at?'':'unread' }}"><div class="fw-semibold small">{{ $notification->data['title'] ?? 'Market update' }}</div><div class="small text-muted">{{ $notification->data['message'] ?? '' }}</div><div class="small text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</div></div>
        @empty<div class="user-empty"><i class="bi bi-bell"></i>No notifications yet.</div>@endforelse
      </div>
    </div>
  </div>
</section>

<section class="row g-3 mt-0">
  <div class="col-12">
    <div id="profile" class="user-panel">
      <div class="user-panel-head"><h3>Applicant information</h3></div>
      @if($reg)
        <div class="user-panel-body">
          <div class="user-profile-grid mb-3">
            <div><strong class="text-dark">Civil status:</strong> {{ $reg->status }}</div><div><strong class="text-dark">Citizenship:</strong> {{ $reg->citizenship }}</div>
            <div><strong class="text-dark">Date of birth:</strong> {{ $reg->birthdate?->format('M d, Y') }}@if($reg->age) ({{ $reg->age }})@endif</div><div><strong class="text-dark">Contact:</strong> {{ $reg->contact_number }}</div>
            <div class="col-span-2"><strong class="text-dark">Address:</strong> {{ $reg->address }}</div>
          </div>
          <details><summary class="text-primary fw-semibold" style="cursor:pointer">Update personal information</summary>
            <form method="POST" action="{{ route('my.profile.update') }}" class="pt-3">@csrf @method('PUT')
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="profile-name">Complete name</label><input id="profile-name" name="name" class="form-control" value="{{ old('name',$reg->name) }}" required></div>
                <div class="col-md-6"><label class="form-label" for="profile-email">Email</label><input id="profile-email" name="email" type="email" class="form-control" value="{{ old('email',$reg->user?->email) }}" required></div>
                <div class="col-md-3"><label class="form-label" for="profile-status">Civil status</label><select id="profile-status" name="status" class="form-select">@foreach(['Single','Married','Widowed','Separated','Divorced'] as $s)<option @selected(old('status',$reg->status)===$s)>{{ $s }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label" for="profile-citizenship">Citizenship</label><input id="profile-citizenship" name="citizenship" class="form-control" value="{{ old('citizenship',$reg->citizenship) }}" required></div>
                <div class="col-md-3"><label class="form-label" for="profile-birthdate">Date of birth</label><input id="profile-birthdate" type="date" name="birthdate" max="{{ today()->subYears(18)->toDateString() }}" class="form-control" value="{{ old('birthdate',$reg->birthdate?->format('Y-m-d')) }}" required></div>
                <div class="col-md-3"><label class="form-label" for="profile-contact">Contact number</label><input id="profile-contact" name="contact_number" class="form-control" value="{{ old('contact_number',$reg->contact_number) }}" required></div>
                <div class="col-12"><label class="form-label" for="profile-address">Residential address</label><input id="profile-address" name="address" class="form-control" value="{{ old('address',$reg->address) }}" required></div>
              </div>
              <button class="btn btn-primary mt-3">Save profile</button><span class="small text-muted ms-2">Control number and approved assignments cannot be edited.</span>
            </form>
          </details>
        </div>
      @else
        <div class="user-empty">No registration profile is linked to this account. Contact the municipal market administrator for assistance.</div>
      @endif
    </div>
  </div>
</section>
@endsection
