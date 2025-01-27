@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card p-4">
            <div class="title_container text-center mb-4">
                <h3 class="title">Register Your Account</h3>
            </div>
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3 input_container">
                    <label for="name" class="input_label">Name</label>
                    <div class="input-group">
                        <input id="name" type="text"
                               class="form-control input_field @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="John Doe">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 input_container">
                    <label for="email" class="input_label">Email Address</label>
                    <div class="input-group">
                        <input id="email" type="email"
                               class="form-control input_field @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@mail.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 input_container">
                    <label for="password" class="input_label">Password</label>
                    <div class="input-group">
                        <input id="password" type="password"
                               class="form-control input_field @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password" placeholder="********">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 input_container">
                    <label for="password_confirmation" class="input_label">Confirm Password</label>
                    <div class="input-group">
                        <input id="password_confirmation" type="password"
                               class="form-control input_field @error('password_confirmation') is-invalid @enderror"
                               name="password_confirmation" required autocomplete="new-password" placeholder="********">
                        @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary sign-in_btn w-100">Register</button>
                </div>

                <div class="text-center" style="margin-top: 1em">
                    <button type="button" class="btn btn-light sign-in_ggl w-100">
                        <img src="/logos/google.png" alt="Google" style="width: 18px; height: 18px; margin-right: 8px;">
                        Sign up with Google
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
