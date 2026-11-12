@extends('layouts.app')
@section('title','Administrator Settings')
@section('content')
<div class="row g-4">
  <div class="col-xl-8">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3"><h2 class="h6 mb-0 fw-bold">Account settings</h2></div>
      <div class="card-body p-4">
        <p class="text-secondary">Update the administrator account used to access the municipal market portal.</p>
        <form method="POST" action="{{ route('admin.settings.update') }}">
          @csrf @method('PUT')
          <div class="mb-3"><label class="form-label" for="name">Name</label><input id="name" name="name" value="{{ old('name',$user->name) }}" class="form-control" maxlength="150" required autocomplete="name"></div>
          <div class="mb-4"><label class="form-label" for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email',$user->email) }}" class="form-control" required autocomplete="email"></div>
          <hr>
          <h3 class="h6 fw-bold mt-4">Change password <span class="text-muted fw-normal">(optional)</span></h3>
          <p class="small text-secondary">Use at least 12 characters. Your current password is required to save a new one.</p>
          <div class="mb-3"><label class="form-label" for="current_password">Current password</label><input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password"></div>
          <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label" for="password">New password</label><input id="password" name="password" type="password" class="form-control" autocomplete="new-password"></div>
            <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password"></div>
          </div>
          <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>Save account settings</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-3"><span class="app-user-avatar"><i class="bi bi-shield-lock"></i></span><div><h2 class="h6 fw-bold mb-1">Administrator access</h2><span class="badge text-bg-success">Protected account</span></div></div>
        <p class="small text-secondary mb-0">Administrative routes require an authenticated administrator account. Changes to this profile are recorded in the activity log.</p>
      </div>
    </div>
  </div>
</div>
@endsection
