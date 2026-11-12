@extends('layouts.auth')
@section('title','Administrator Sign In')
@section('icon','bi-shield-lock')
@section('eyebrow','Authorized personnel')
@section('heading','Administrator sign in')
@section('description','Use your municipal administrator credentials to access market management tools.')
@section('form')
<form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
  @csrf
  <div class="mb-3">
    <label class="form-label" for="login">Username or email</label>
    <div class="auth-input-icon"><i class="bi bi-person-badge"></i><input id="login" name="login" type="text" value="{{ old('login') }}" class="form-control" required autofocus autocomplete="username" placeholder="Administrator username or email"></div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <div class="auth-input-icon"><i class="bi bi-lock"></i><input id="password" type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Enter your password"><button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
  </div>
  <div class="form-check mb-4"><input type="checkbox" name="remember" class="form-check-input" id="remember" value="1" @checked(old('remember'))><label for="remember" class="form-check-label small">Remember me</label></div>
  <button class="btn btn-success auth-primary w-100" type="submit"><i class="bi bi-shield-check me-2"></i>Sign In to Admin Portal</button>
</form>
@endsection
@section('links')
<div class="auth-links"><a href="{{ route('login') }}"><i class="bi bi-person me-1"></i>Applicant sign in</a><a href="{{ route('landing') }}"><i class="bi bi-house me-1"></i>Home</a></div>
@endsection
