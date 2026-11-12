@extends('layouts.auth')
@section('title','Applicant Sign In')
@section('icon','bi-person-check')
@section('eyebrow','Applicant access')
@section('heading','Welcome back')
@section('description','Sign in to track applications, view your stall assignments, and manage lease documents.')
@section('form')
<form method="POST" action="{{ route('login') }}" novalidate>
  @csrf
  <div class="mb-3">
    <label class="form-label" for="login">Email address or username</label>
    <div class="auth-input-icon"><i class="bi bi-person"></i><input id="login" name="login" type="text" value="{{ old('login') }}" class="form-control" required autofocus autocomplete="username" placeholder="you@example.com"></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <div class="auth-input-icon"><i class="bi bi-lock"></i><input id="password" type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Enter your password"><button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
  </div>
  <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
    <div class="form-check"><input type="checkbox" name="remember" class="form-check-input" id="remember" value="1" @checked(old('remember'))><label for="remember" class="form-check-label small">Remember me</label></div>
    <a class="small text-decoration-none" href="{{ route('password.request') }}">Forgot password?</a>
  </div>
  <button class="btn btn-success auth-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
</form>
@endsection
@section('links')
<div class="auth-links"><a href="{{ route('landing') }}#register"><i class="bi bi-person-plus me-1"></i>Create an account</a><a href="{{ route('landing') }}"><i class="bi bi-house me-1"></i>Home</a></div>
<div class="text-center mt-3"><a class="small text-decoration-none text-secondary" href="{{ route('admin.login') }}">Administrator sign in</a></div>
@endsection
