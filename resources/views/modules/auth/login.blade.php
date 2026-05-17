@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                @if($logo = setting('school_logo'))
                    <img src="{{ $logo }}" alt="{{ setting('school_name', 'School ERP') }}" style="max-height:60px;" class="mb-2">
                @endif
                <div class="fs-3 fw-bold">{{ setting('school_name', 'School ERP') }}</div>
                <div class="text-secondary small mt-1">Sign in to your school workspace</div>
            </div>

            @include('layouts.partials.flash')

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label required" for="email">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-mail"></i></span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus placeholder="Enter your email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label required" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-lock"></i></span>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Enter your password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="link-primary text-decoration-none small fw-medium">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="ti ti-login me-1"></i> Login
                </button>
            </form>
        </div>
    </div>
@endsection
