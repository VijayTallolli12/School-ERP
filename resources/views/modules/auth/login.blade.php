@extends('layouts.guest')
@section('body_class', 'p-0 m-0 bg-white')
@section('main_class', 'p-0 m-0 w-100')

@section('title', 'Login')

@section('content')
<x-auth-layout title="Login to your account" subtitle="Enter your registered email address and password to login">
    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="mb-4 position-relative">
            <label class="form-label mb-2" for="email" style="color: var(--auth-text); font-weight: 500; font-size: 14px;">Email Address</label>
            <div class="position-relative">
                <!-- <span class="material-symbols-rounded position-absolute top-50 translate-middle-y ms-3" style="color: var(--auth-text-muted); z-index: 4; font-size: 22px;">mail</span> -->
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus placeholder="admin@school.com" style="padding-left: 2.75rem; padding-right: 1rem; padding-top: 0.75rem; padding-bottom: 0.75rem; border-color: var(--auth-border); border-radius: 8px; font-size: 14px; color: var(--auth-text);">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-4 position-relative">
            <label class="form-label mb-2" for="password" style="color: var(--auth-text); font-weight: 500; font-size: 14px;">Password</label>
            <div class="position-relative">
                <!-- <span class="material-symbols-rounded position-absolute top-50 translate-middle-y ms-3" style="color: var(--auth-text-muted); z-index: 4; font-size: 22px;">key</span> -->
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="••••••••••••" style="padding-left: 2.75rem; padding-right: 2.75rem; padding-top: 0.75rem; padding-bottom: 0.75rem; border-color: var(--auth-border); border-radius: 8px; font-size: 14px; color: var(--auth-text);">
                <span class="material-symbols-rounded position-absolute top-50 translate-middle-y me-3 end-0" style="color: var(--auth-text-muted); z-index: 4; font-size: 22px; cursor: pointer;" onclick="const pwd = document.getElementById('password'); if(pwd.type === 'password') { pwd.type = 'text'; this.textContent = 'visibility'; } else { pwd.type = 'password'; this.textContent = 'visibility_off'; }">visibility_off</span>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
            <div class="form-check d-flex align-items-center m-0 p-0" style="padding-left: 1.5rem !important;">
                <input class="form-check-input mt-0 shadow-none" type="checkbox" name="remember" value="1" id="remember" style="border-color: var(--auth-border); border-radius: 4px; width: 18px; height: 18px; margin-left: -1.5rem;">
                <label class="form-check-label ms-2" for="remember" style="color: var(--auth-text-muted); font-size: 14px;">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: var(--auth-primary); font-weight: 500; font-size: 14px; transition: color 0.2s ease-in-out;" onmouseover="this.style.color='var(--auth-primary-hover)'" onmouseout="this.style.color='var(--auth-primary)'">Forgot Password ?</a>
        </div>

        <button type="submit" class="w-100 mt-2 d-flex justify-content-center align-items-center" style="background-color: var(--auth-primary); color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; height: 48px; cursor: pointer; transition: all 0.2s ease-in-out;" onmouseover="this.style.backgroundColor='var(--auth-primary-hover)'" onmouseout="this.style.backgroundColor='var(--auth-primary)'">
            Login
        </button>
    </form>
</x-auth-layout>
@endsection
