@extends('layouts.guest')
@section('body_class', 'p-0 m-0 bg-white')
@section('main_class', 'p-0 m-0 w-100')

@section('title', 'Forgot Password')

@section('content')
<x-auth-layout title="Forgot Password?" subtitle="Reset your password">
    <p class="mb-4" style="color: var(--auth-text-muted); font-size: 14px;">Enter your email address and we'll send you instructions to reset your password.</p>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4 position-relative">
            <label class="form-label mb-2" for="email" style="color: var(--auth-text); font-weight: 500; font-size: 14px;">Email Address</label>
            <div class="position-relative">
                <!-- <span class="material-symbols-rounded position-absolute top-50 translate-middle-y ms-3" style="color: var(--auth-text-muted); z-index: 4; font-size: 22px;">mail</span> -->
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus placeholder="admin@example.com" style="padding-left: 2.75rem; padding-right: 1rem; padding-top: 0.75rem; padding-bottom: 0.75rem; border-color: var(--auth-border); border-radius: 8px; font-size: 14px; color: var(--auth-text);">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-grid gap-3 mt-4">
            <button type="submit" class="w-100 d-flex justify-content-center align-items-center" style="background-color: var(--auth-primary); color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; height: 48px; cursor: pointer; transition: all 0.2s ease-in-out;" onmouseover="this.style.backgroundColor='var(--auth-primary-hover)'" onmouseout="this.style.backgroundColor='var(--auth-primary)'">
                Send Reset Link
            </button>
            <a class="text-decoration-none text-center" href="{{ route('login') }}" style="color: var(--auth-primary); font-weight: 500; font-size: 14px; transition: color 0.2s ease-in-out;" onmouseover="this.style.color='var(--auth-primary-hover)'" onmouseout="this.style.color='var(--auth-primary)'">
                <span class="material-symbols-rounded align-middle" style="font-size: 18px; margin-right: 4px;">arrow_back</span> Back to Login
            </a>
        </div>
    </form>
</x-auth-layout>
@endsection
