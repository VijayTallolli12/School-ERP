@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                @if($logo = setting('school_logo'))
                    <img src="{{ $logo }}" alt="{{ setting('school_name', 'School ERP') }}" style="max-height:60px;" class="mb-2">
                @endif
                <div class="fs-3 fw-bold">{{ setting('school_name', 'School ERP') }}</div>
            </div>

            <h1 class="h5 fw-semibold mb-3">Create new password</h1>

            @include('layouts.partials.flash')

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label required" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control @error('email') is-invalid @enderror" required placeholder="Enter your email">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label required" for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Enter new password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label required" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required placeholder="Re-enter new password">
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">
                    <i class="ti ti-key me-1"></i> Reset password
                </button>
            </form>
        </div>
    </div>
@endsection
