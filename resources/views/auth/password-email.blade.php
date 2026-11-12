@extends('layouts.auth')
@section('title','Reset Password')
@section('icon','bi-key')
@section('eyebrow','Account recovery')
@section('heading','Forgot your password?')
@section('description','Enter the email address linked to your applicant account. We will email a secure password reset link if the account is registered.')
@section('form')
<form method="POST" action="{{ route('password.email') }}">
  @csrf
  <div class="mb-4">
    <label class="form-label" for="email">Email address</label>
    <div class="auth-input-icon"><i class="bi bi-envelope"></i><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="email" placeholder="you@example.com"></div>
  </div>
  <button class="btn btn-success auth-primary w-100" type="submit"><i class="bi bi-envelope-arrow-up me-2"></i>Email Password Reset Link</button>
</form>
@endsection
@section('links')
<div class="auth-links"><a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a><a href="{{ route('landing') }}"><i class="bi bi-house me-1"></i>Home</a></div>
@endsection
