@extends('layouts.auth')
@section('title','Choose a New Password')
@section('icon','bi-shield-lock')
@section('eyebrow','Secure account recovery')
@section('heading','Choose a new password')
@section('description','Create a new password to securely access your applicant account.')
@section('form')
<form method="POST" action="{{ route('password.update') }}">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <div class="mb-3">
    <label class="form-label" for="email">Email address</label>
    <div class="auth-input-icon"><i class="bi bi-envelope"></i><input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="form-control" required autocomplete="email"></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="password">New password</label>
    <div class="auth-input-icon"><i class="bi bi-lock"></i><input id="password" type="password" name="password" class="form-control" required autocomplete="new-password" placeholder="At least 8 characters"><button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
  </div>
  <div class="mb-4">
    <label class="form-label" for="password_confirmation">Confirm new password</label>
    <div class="auth-input-icon"><i class="bi bi-lock-fill"></i><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="Re-enter your new password"><button class="auth-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
  </div>
  <button class="btn btn-success auth-primary w-100" type="submit"><i class="bi bi-check2-circle me-2"></i>Reset Password</button>
</form>
@endsection
@section('links')
<div class="auth-links"><a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a><a href="{{ route('landing') }}"><i class="bi bi-house me-1"></i>Home</a></div>
@endsection
