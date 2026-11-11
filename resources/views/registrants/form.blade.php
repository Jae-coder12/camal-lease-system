@extends('layouts.app')
@section('title', $registrant->exists ? 'Edit Registrant' : 'Add Registrant')
@section('content')
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="{{ $registrant->exists ? route('registrants.update',$registrant) : route('registrants.store') }}">
  @csrf @if($registrant->exists) @method('PUT') @endif
  @include('partials.registrant-fields')
  <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('registrants.index') }}" class="btn btn-link">Cancel</a></div>
</form></div></div>
@endsection
