@extends('layouts.app')
@section('title', $stall->exists ? 'Edit Stall' : 'Add Stall')
@section('content')
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ $stall->exists ? route('stalls.update',$stall) : route('stalls.store') }}">
  @csrf @if($stall->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Building</label><select name="building_id" class="form-select" required>
      <option value="">Select building</option>@foreach($buildings as $building)<option value="{{ $building->id }}" @selected(old('building_id',$stall->building_id)==$building->id)>{{ $building->name }}</option>@endforeach
    </select></div>
    <div class="col-md-3"><label class="form-label">Stall No.</label><input name="stall_number" class="form-control" value="{{ old('stall_number',$stall->stall_number) }}" required></div>
    <div class="col-md-5"><label class="form-label">Location</label><input name="location" class="form-control" value="{{ old('location',$stall->location) }}" placeholder="Defaults to building"></div>
    <div class="col-md-2"><label class="form-label">Size (m²)</label><input type="number" step="0.01" name="size_sqm" class="form-control" value="{{ old('size_sqm',$stall->size_sqm) }}" required></div>
    <div class="col-md-3"><label class="form-label">Rental Rate (₱)</label><input type="number" step="0.01" min="0" name="rental_rate" class="form-control" value="{{ old('rental_rate',$stall->rental_rate ?? $stall->monthly_rate) }}" required></div>
    <div class="col-md-3"><label class="form-label">Payment Frequency</label><select name="payment_frequency" class="form-select" required><option value="monthly" @selected(old('payment_frequency',$stall->payment_frequency)==='monthly')>Monthly</option><option value="daily" @selected(old('payment_frequency',$stall->payment_frequency)==='daily')>Daily-Cash Ticket</option></select></div>
    <div class="col-md-3"><label class="form-label">Stall type</label><input name="stall_type" class="form-control" value="{{ old('stall_type',$stall->stall_type) }}"></div>
    <div class="col-md-3"><label class="form-label">Shared capacity</label><input type="number" min="1" max="20" name="shared_capacity" class="form-control" value="{{ old('shared_capacity',$stall->shared_capacity ?? 1) }}" required></div>
    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="available" @selected(old('status',$stall->status)==='available')>Available</option><option value="inactive" @selected(old('status',$stall->status)==='inactive')>Inactive / verify</option></select></div>
    <div class="col-md-3 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="needs_verification" value="1" id="verify" @checked(old('needs_verification',$stall->needs_verification))><label class="form-check-label" for="verify">Needs verification</label></div></div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description',$stall->description) }}</textarea></div>
    <div class="col-12"><label class="form-label">Source / verification notes</label><textarea name="source_notes" class="form-control" rows="2">{{ old('source_notes',$stall->source_notes) }}</textarea></div>
    <div class="col-md-6"><label class="form-label">Stall Owner</label>
      <select name="registrant_id" class="form-select">
        <option value="">— None (Available) —</option>
        @foreach($registrants as $r)<option value="{{ $r->id }}" @selected(old('registrant_id',$stall->registrant_id)==$r->id)>{{ $r->control_number }} — {{ $r->name }}</option>@endforeach
      </select>
      <div class="form-text">Changing the owner preserves the prior lease and creates a new lease record.</div></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('stalls.index') }}" class="btn btn-link">Cancel</a></div>
</form></div></div>
@endsection
