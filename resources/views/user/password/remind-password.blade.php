@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Forgot Password') }}</div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card-body">
                        <form method="POST" action="{{ route('fpbx.user.forgot-password') }}">
                            @csrf
                            <div class="form-group row">
                                <label for="domain_name"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Domain name') }}</label>
                                <div class="col-md-6">
                                    <input id="domain_name" type="text"
                                        class="form-control {{ $errors->has('domain_name') ? ' is-invalid' : '' }}"
                                        name="domain_name" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="user_email"
                                    class="col-md-4 col-form-label text-md-right">{{ __('User email') }}</label>
                                <div class="col-md-6">
                                    <input id="user_email" type="email"
                                        class="form-control {{ $errors->has('user_email') ? ' is-invalid' : '' }}"
                                        name="user_email" required>
                                </div>
                            </div>


                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Send link') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
