@extends('layouts.app')
@section('title','Activity Log')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
  <div><h2 class="h5 mb-1 text-primary fw-bold">Administrative activity</h2><p class="small text-muted mb-0">Auditable actions recorded by the market management system.</p></div>
  <form method="GET" class="d-flex gap-2" role="search">
    <label class="visually-hidden" for="activity-search">Search activity</label>
    <input id="activity-search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search action or user">
    <button class="btn btn-outline-primary" type="submit">Search</button>
    @if(request('q'))<a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-secondary">Clear</a>@endif
  </form>
</div>
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead><tr><th>Date and time</th><th>Action</th><th>Administrator</th><th>Related record</th><th>Details</th></tr></thead>
      <tbody>
      @forelse($activities as $activity)
        <tr>
          <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($activity->created_at)->format('M d, Y · g:i A') }}</td>
          <td><span class="badge text-bg-light">{{ str($activity->action)->replace(['.','_'], ' ')->title() }}</span></td>
          <td>{{ $activity->actor_name ?? 'System' }}</td>
          <td>{{ $activity->subject_type ? class_basename($activity->subject_type) : '—' }}@if($activity->subject_id) <span class="text-muted">#{{ $activity->subject_id }}</span>@endif</td>
          <td class="small text-muted">{{ $activity->details ?: '—' }}</td>
        </tr>
      @empty<tr><td colspan="5" class="text-center text-muted py-4">No activity records match this search.</td></tr>@endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">{{ $activities->links('pagination::bootstrap-5') }}</div>
@endsection
