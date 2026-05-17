@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                @if($logo = setting('school_logo'))
                    <img src="{{ $logo }}" alt="{{ setting('school_name', 'School ERP') }}" style="max-height:60px;" class="mb-2">
                @endif
                <div class="fs-3 fw-bold">{{ setting('school_name', 'School ERP') }}</div>
            </div>

            <h1 class="h5 fw-semibold mb-2">Reset password</h1>
            <p class="text-secondary small mb-4">Enter your email address and we will send a reset link.</p>

            @include('layouts.partials.flash')

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label required" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus placeholder="Enter your email">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary py-2" type="submit">
                        <i class="ti ti-send me-1"></i> Send reset link
                    </button>
                    <a class="btn btn-link text-decoration-none" href="{{ route('login') }}">
                        <i class="ti ti-arrow-left me-1"></i> Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
