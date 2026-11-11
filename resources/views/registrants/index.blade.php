@extends('layouts.app')
@section('title','Registrants')
@section('content')
<div class="d-flex justify-content-between mb-3">
  <form class="d-flex gap-2"><input name="q" value="{{ $q }}" class="form-control" placeholder="Search name or control no."><button class="btn btn-outline-secondary">Search</button></form>
  <a href="{{ route('registrants.create') }}" class="btn btn-primary">+ Add Registrant</a>
</div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
<thead><tr><th>Control #</th><th>Name</th><th>Status</th><th>Citizenship</th><th>Age</th><th>Contact</th><th></th></tr></thead><tbody>
@forelse($registrants as $r)
<tr><td>{{ $r->control_number }}</td><td>{{ $r->name }}<br><small class="text-muted">{{ $r->address }}</small></td><td>{{ $r->status }}</td><td>{{ $r->citizenship }}</td><td>{{ $r->age }}</td><td>{{ $r->contact_number }}</td>
<td class="text-end text-nowrap"><a href="{{ route('registrants.edit',$r) }}" class="btn btn-sm btn-outline-primary">Edit</a>
<form method="POST" action="{{ route('registrants.destroy',$r) }}" class="d-inline" onsubmit="return confirm('Delete this registrant? Their stalls will become available.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
@empty <tr><td colspan="7" class="text-center text-muted">No registrants found.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $registrants->links('pagination::bootstrap-5') }}</div>
@endsection
