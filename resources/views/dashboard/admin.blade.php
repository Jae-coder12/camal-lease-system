@extends('layouts.app')
@section('title','Dashboard')
@section('content')
@php
  $statMeta = [
    'Total Buildings' => ['bi-buildings', route('buildings.index'), 'mint'],
    'Total Market Stalls' => ['bi-grid-3x3-gap', route('stalls.index'), 'blue'],
    'Occupied Stalls' => ['bi-shop', route('stalls.index'), 'amber'],
    'Available Stalls' => ['bi-check2-circle', route('stalls.index'), 'green'],
    'Pending Applications' => ['bi-hourglass-split', route('applications.index', ['status' => 'pending']), 'amber'],
    'Pending Transactions' => ['bi-receipt', route('payments.index'), 'amber'],
    'Applications Under Review' => ['bi-search', route('applications.index', ['status' => 'under_review']), 'blue'],
    'Approved Applications' => ['bi-check-circle', route('applications.index', ['status' => 'approved']), 'green'],
    'Rejected Applications' => ['bi-x-circle', route('applications.index', ['status' => 'rejected']), 'red'],
    'Completed Transactions' => ['bi-clipboard-check', route('payments.index', ['status' => 'paid']), 'green'],
    'Active Lease Contracts' => ['bi-file-earmark-check', route('leases.index', ['status' => 'active']), 'green'],
    'Expiring Lease Contracts' => ['bi-alarm', route('leases.index', ['status' => 'expiring']), 'amber'],
    'Expired Lease Contracts' => ['bi-calendar-x', route('leases.index', ['status' => 'expired']), 'red'],
    'Total Registrants' => ['bi-people', route('registrants.index'), 'blue'],
  ];
  $applicationTotal = array_sum($applicationCounts);
  $buildingTotal = $buildings->sum('stalls_count');
  $occupiedTotal = $buildings->sum('occupied_stalls_count');
  $availableTotal = $buildings->sum('available_stalls_count');
@endphp
<style>
  .dash-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;margin-bottom:1.25rem}
  .dash-heading h2{margin:0;color:#12324a;font-size:1.28rem;font-weight:750}
  .dash-heading p{margin:.28rem 0 0;color:#64748b;font-size:.88rem}
  .dash-shortcuts{display:flex;flex-wrap:wrap;gap:.5rem}
  .dash-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem;margin-bottom:1.25rem}
  .dash-stat{display:flex;min-width:0;min-height:92px;align-items:center;gap:.8rem;padding:.9rem 1rem;border:1px solid #e6ebf0;border-radius:13px;background:#fff;box-shadow:0 4px 16px rgba(18,50,74,.045);color:inherit;text-decoration:none;transition:transform .16s,box-shadow .16s}
  .dash-stat:hover{transform:translateY(-2px);box-shadow:0 9px 22px rgba(18,50,74,.09)}
  .dash-stat-icon{display:grid;flex:0 0 40px;width:40px;height:40px;place-items:center;border-radius:11px;font-size:1.05rem}
  .dash-stat-icon.mint,.dash-stat-icon.green{background:#e7f3ed;color:#198754}.dash-stat-icon.blue{background:#eaf2f8;color:#25658a}.dash-stat-icon.amber{background:#fff4de;color:#9a6700}.dash-stat-icon.red{background:#fdecec;color:#b42318}
  .dash-stat-value{color:#12324a;font-size:1.4rem;font-weight:780;line-height:1.1}
  .dash-stat-label{margin-top:.28rem;color:#64748b;font-size:.76rem;line-height:1.25}
  .dash-panel{height:100%;border:1px solid #e4eaf0;border-radius:13px;background:#fff;box-shadow:0 5px 18px rgba(18,50,74,.045)}
  .dash-panel-head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.9rem 1rem;border-bottom:1px solid #edf1f4}
  .dash-panel-title{margin:0;color:#12324a;font-size:.91rem;font-weight:750}
  .dash-panel-body{padding:1rem}
  .dash-chart{position:relative;width:100%;height:250px}
  .dash-empty{display:grid;min-height:150px;place-items:center;color:#718096;text-align:center}
  .dash-empty i{display:block;margin-bottom:.45rem;color:#9aabba;font-size:1.5rem}
  .dash-section{margin-top:1.1rem}
  .dash-table{margin:0;font-size:.84rem}
  .dash-table td,.dash-table th{padding:.68rem .8rem;vertical-align:middle}
  .dash-activity{display:flex;gap:.7rem;padding:.75rem 1rem;border-bottom:1px solid #edf1f4}
  .dash-activity:last-child{border-bottom:0}
  .dash-activity-dot{flex:0 0 8px;width:8px;height:8px;margin-top:.4rem;border-radius:50%;background:#198754}
  .dash-expiry-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.72rem 0;border-bottom:1px solid #edf1f4}
  .dash-expiry-row:last-child{border-bottom:0}
  @media(max-width:1100px){.dash-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
  @media(max-width:700px){.dash-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}.dash-stat{min-height:80px;padding:.7rem}.dash-stat-icon{flex-basis:35px;width:35px;height:35px}.dash-stat-value{font-size:1.2rem}.dash-heading{align-items:flex-start;flex-direction:column}.dash-chart{height:220px}}
  @media(max-width:370px){.dash-stats{grid-template-columns:1fr}}
</style>

<div class="dash-heading">
  <div><h2>Market operations overview</h2><p>Live status across market facilities, applications, leases, and payments.</p></div>
  <div class="dash-shortcuts">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('applications.index') }}"><i class="bi bi-inbox me-1"></i>Review applications</a>
    <a class="btn btn-sm btn-success" href="{{ route('payments.index') }}"><i class="bi bi-cash-stack me-1"></i>Payments</a>
  </div>
</div>

<section class="dash-stats" aria-label="Market summary">
  @foreach($stats as $label => $value)
    @php [$icon, $url, $tone] = $statMeta[$label] ?? ['bi-bar-chart', '#reports', 'blue']; @endphp
    <a class="dash-stat" href="{{ $url }}">
      <span class="dash-stat-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
      <span class="min-w-0"><span class="dash-stat-value d-block">{{ number_format($value) }}</span><span class="dash-stat-label d-block">{{ $label }}</span></span>
    </a>
  @endforeach
</section>

<section id="reports" class="row g-3">
  <div class="col-xl-7">
    <div class="dash-panel">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Stall occupancy by building</h3><a href="{{ route('buildings.index') }}" class="small text-decoration-none">Buildings</a></div>
      <div class="dash-panel-body">
        @if($buildings->isEmpty())<div class="dash-empty"><div><i class="bi bi-buildings"></i>No active building data is available.</div></div>
        @else<div class="dash-chart"><canvas id="occupancyChart" aria-label="Occupied and available stalls by building"></canvas></div>@endif
      </div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="dash-panel">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Applications by status</h3><a href="{{ route('applications.index') }}" class="small text-decoration-none">View all</a></div>
      <div class="dash-panel-body">
        @if($applicationTotal === 0)<div class="dash-empty"><div><i class="bi bi-inbox"></i>No applications have been submitted.</div></div>
        @else<div class="dash-chart"><canvas id="applicationChart" aria-label="Applications grouped by status"></canvas></div>@endif
      </div>
    </div>
  </div>
  <div class="col-xl-7">
    <div class="dash-panel">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Monthly recorded rental income</h3><a href="{{ route('payments.index') }}" class="small text-decoration-none">Payment records</a></div>
      <div class="dash-panel-body">
        @if(!collect($monthlyIncome)->contains(fn ($amount) => $amount > 0))<div class="dash-empty"><div><i class="bi bi-cash-coin"></i>No paid rental transactions were recorded in the displayed period.</div></div>
        @else<div class="dash-chart"><canvas id="incomeChart" aria-label="Monthly rental income in Philippine pesos"></canvas></div>@endif
      </div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="dash-panel">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Lease contract monitoring</h3><a href="{{ route('leases.index') }}" class="small text-decoration-none">Lease contracts</a></div>
      <div class="dash-panel-body">
        @if($leaseMonitoringTotal === 0)<div class="dash-empty"><div><i class="bi bi-file-earmark-text"></i>No lease contracts to monitor yet.</div></div>
        @else<div class="dash-chart"><canvas id="leaseChart" aria-label="Active, expiring, and expired leases"></canvas></div>@endif
      </div>
    </div>
  </div>
</section>

<section class="row g-3 dash-section">
  <div class="col-12">
    <div class="dash-panel">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Recently submitted applications</h3><a href="{{ route('applications.index') }}" class="small text-decoration-none">View all</a></div>
      <div class="table-responsive"><table class="table dash-table">
        <thead><tr><th>Applicant</th><th>Building / stall</th><th>Purpose</th><th>Status</th><th>Submitted</th></tr></thead>
        <tbody>
        @forelse($recent as $application)
          <tr><td class="fw-semibold">{{ $application->registrant->name }}<span class="d-block small text-muted">{{ $application->registrant->control_number }}</span></td>
          <td>{{ $application->stall->building?->code }} / {{ $application->stall->stall_number }}</td><td>{{ $application->purpose }}</td>
          <td><span class="badge rounded-pill bg-{{ ['pending'=>'warning','under_review'=>'info','approved'=>'success','rejected'=>'danger','completed'=>'primary','cancelled'=>'secondary'][$application->status] ?? 'secondary' }}">{{ str($application->status)->replace('_',' ')->title() }}</span></td>
          <td class="text-nowrap">{{ $application->created_at->format('M d, Y') }}</td></tr>
        @empty<tr><td colspan="5"><div class="dash-empty">No applications have been submitted.</div></td></tr>@endforelse
        </tbody>
      </table></div>
    </div>
  </div>
</section>

<section class="row g-3 dash-section">
  <div class="col-xl-7">
    <div class="dash-panel h-100">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Recent transactions</h3><a href="{{ route('payments.index') }}" class="small text-decoration-none">View all</a></div>
      <div class="table-responsive"><table class="table dash-table">
        <thead><tr><th>Reference</th><th>Applicant / stall</th><th>Amount</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($recentPayments as $payment)
          <tr><td class="text-nowrap">{{ $payment->reference_number }}</td>
          <td>{{ $payment->lease->registrant->name }}<span class="d-block small text-muted">{{ $payment->lease->stall->building?->code }} / {{ $payment->lease->stall->stall_number }}</span></td>
          <td class="text-nowrap">₱{{ number_format((float)$payment->amount,2) }}</td>
          <td><span class="badge rounded-pill bg-{{ $payment->status==='paid'?'success':($payment->status==='pending'?'warning':($payment->status==='rejected'?'danger':'secondary')) }}">{{ str($payment->status)->title() }}</span></td>
          <td>@if($payment->status==='paid')<a href="{{ route('payments.receipt',$payment) }}" class="btn btn-sm btn-outline-primary">Receipt</a>@else<a href="{{ route('payments.index',['status'=>$payment->status]) }}" class="btn btn-sm btn-outline-primary">Review</a>@endif</td></tr>
        @empty<tr><td colspan="5"><div class="dash-empty">No payment transactions recorded.</div></td></tr>@endforelse
        </tbody>
      </table></div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="dash-panel h-100">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Upcoming lease expirations</h3><a href="{{ route('leases.index',['status'=>'expiring']) }}" class="small text-decoration-none">View all</a></div>
      <div class="dash-panel-body">
        @forelse($expiringLeases as $lease)
          @php $daysRemaining = today()->diffInDays($lease->end_date,false); @endphp
          <div class="dash-expiry-row"><div class="min-w-0"><div class="fw-semibold text-truncate">{{ $lease->registrant->name }}</div><div class="small text-muted">{{ $lease->stall->building?->code }} / {{ $lease->stall->stall_number }} · {{ $lease->end_date->format('M d, Y') }}</div></div><span class="badge rounded-pill text-bg-warning text-nowrap">{{ $daysRemaining }} days</span></div>
        @empty<div class="dash-empty">No active leases are expiring within {{ config('market.lease_warning_days',30) }} days.</div>@endforelse
      </div>
    </div>
  </div>
</section>

<section class="row g-3 dash-section">
  <div class="col-xl-5">
    <div class="dash-panel h-100">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Recently registered applicants</h3><a href="{{ route('registrants.index') }}" class="small text-decoration-none">View all</a></div>
      <div class="table-responsive"><table class="table dash-table">
        <thead><tr><th>Applicant</th><th>Control number</th><th>Registered</th></tr></thead>
        <tbody>@forelse($recentRegistrants as $registrant)
          <tr><td><a href="{{ route('registrants.show',$registrant) }}" class="text-decoration-none fw-semibold">{{ $registrant->name }}</a><span class="d-block small text-muted">{{ $registrant->user?->email }}</span></td><td class="text-nowrap">{{ $registrant->control_number }}</td><td class="text-nowrap">{{ $registrant->created_at->format('M d, Y') }}</td></tr>
        @empty<tr><td colspan="3"><div class="dash-empty">No applicants have registered.</div></td></tr>@endforelse</tbody>
      </table></div>
    </div>
  </div>
  <div class="col-xl-4">
    <div id="notifications" class="dash-panel h-100">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Notifications @if($unreadNotifications)<span class="badge rounded-pill text-bg-success ms-1">{{ $unreadNotifications }}</span>@endif</h3></div>
      <div>
        @forelse($notifications as $notification)
          <div class="dash-activity"><span class="dash-activity-dot" style="{{ $notification->read_at?'background:#aab8c5':'' }}"></span><div class="min-w-0"><div class="fw-semibold small">{{ $notification->data['title'] ?? 'Market update' }}</div><div class="small text-muted">{{ $notification->data['message'] ?? '' }}</div><div class="small text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</div></div></div>
        @empty<div class="dash-empty">No recent notifications.</div>@endforelse
      </div>
    </div>
  </div>
  <div class="col-xl-3">
    <div id="activity" class="dash-panel h-100">
      <div class="dash-panel-head"><h3 class="dash-panel-title">Latest activity</h3><a href="{{ route('admin.activity-logs') }}" class="small text-decoration-none">View all</a></div>
      <div>
        @forelse($activities as $activity)
          <div class="dash-activity"><span class="dash-activity-dot"></span><div class="min-w-0"><div class="fw-semibold small">{{ str($activity->action)->replace('.', ' ')->replace('_', ' ')->title() }}</div><div class="small text-muted">{{ $activity->actor_name ?? 'System' }} · {{ \Illuminate\Support\Carbon::parse($activity->created_at)->diffForHumans() }}</div></div></div>
        @empty<div class="dash-empty">No activity recorded.</div>@endforelse
      </div>
    </div>
  </div>
</section>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const chartOptions = {responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:8,padding:14}}}};
  const occupancyCanvas = document.getElementById('occupancyChart');
  if (occupancyCanvas) {
    new Chart(occupancyCanvas, {type:'bar',data:{labels:@json($buildings->pluck('code')),datasets:[
      {label:'Occupied',data:@json($buildings->pluck('occupied_stalls_count')),backgroundColor:'#12324A',borderRadius:5},
      {label:'Available',data:@json($buildings->pluck('available_stalls_count')),backgroundColor:'#198754',borderRadius:5}
    ]},options:{...chartOptions,scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#edf1f4'}}}}});
  }
  const applicationsCanvas = document.getElementById('applicationChart');
  if (applicationsCanvas) {
    new Chart(applicationsCanvas, {type:'doughnut',data:{labels:['Pending','Under Review','Approved','Rejected','Completed'],datasets:[{
      data:@json($applicationCounts),backgroundColor:['#d99a18','#2482ad','#198754','#c94c4c','#12324A'],borderWidth:0
    }]},options:{...chartOptions,cutout:'68%'}});
  }
  const incomeCanvas = document.getElementById('incomeChart');
  if (incomeCanvas) {
    new Chart(incomeCanvas, {type:'line',data:{labels:@json($monthlyLabels),datasets:[{
      label:'Recorded payments',data:@json($monthlyIncome),borderColor:'#198754',backgroundColor:'rgba(25,135,84,.10)',fill:true,tension:.28,pointRadius:3
    }]},options:{...chartOptions,plugins:{...chartOptions.plugins,legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#edf1f4'},ticks:{callback:value=>'₱'+Number(value).toLocaleString('en-PH')}}}}});
  }
  const leaseCanvas = document.getElementById('leaseChart');
  if (leaseCanvas) {
    new Chart(leaseCanvas, {type:'doughnut',data:{labels:['Active','Expiring soon','Expired'],datasets:[{
      data:@json($leaseMonitoringCounts),backgroundColor:['#198754','#d99a18','#c94c4c'],borderWidth:0
    }]},options:{...chartOptions,cutout:'68%'}});
  }
});
</script>
@endpush
