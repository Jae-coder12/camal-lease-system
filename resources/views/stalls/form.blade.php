@extends('layouts.app')
@section('title', $stall->exists ? 'Edit Stall' : 'Add Stall')
@section('content')
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ $stall->exists ? route('stalls.update',$stall) : route('stalls.store') }}">
  @csrf @if($stall->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Stall No.</label><input name="stall_number" class="form-control" value="{{ old('stall_number',$stall->stall_number) }}" required></div>
    <div class="col-md-5"><label class="form-label">Location</label><input name="location" class="form-control" value="{{ old('location',$stall->location) }}" required></div>
    <div class="col-md-2"><label class="form-label">Size (m²)</label><input type="number" step="0.01" name="size_sqm" class="form-control" value="{{ old('size_sqm',$stall->size_sqm) }}" required></div>
    <div class="col-md-2"><label class="form-label">Monthly Rate (₱)</label><input type="number" step="0.01" name="monthly_rate" class="form-control" value="{{ old('monthly_rate',$stall->monthly_rate) }}" required></div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description',$stall->description) }}</textarea></div>
    <div class="col-md-6"><label class="form-label">Stall Owner</label>
      <select name="registrant_id" class="form-select">
        <option value="">— None (Available) —</option>
        @foreach($registrants as $r)<option value="{{ $r->id }}" @selected(old('registrant_id',$stall->registrant_id)==$r->id)>{{ $r->control_number }} — {{ $r->name }}</option>@endforeach
      </select>
      <div class="form-text">Changing the owner automatically ends the old lease, creates a new one, and updates the stall status.</div></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('stalls.index') }}" class="btn btn-link">Cancel</a></div>
</form></div></div>
@endsection
