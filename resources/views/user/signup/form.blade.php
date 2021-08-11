@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('User signup') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('web.user.process.signup') }}">
                            @csrf


                            <label for="domain_name">Domain name</label>

                            <input id="domain_name" type="text" class="@error('domain_name') is-invalid @enderror" name="domain_name">
                            
                            @error('domain_name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror


                            <div class="form-group row">
                                <label for="password"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>
                                <div class="col-md-6">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            <div class="invalid-feedback d-block">
                                                <strong>{{ $error }}</strong>
                                            </div>
                                        @endforeach
                                    @endif
                                    <input id="password" type="password"
                                    class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                    name="password" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password-confirm"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Reset Password') }}
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



{{-- {{ $a = $domains->first() }} --}}
{{-- {{ d($a )}} --}}
{{ $domains[0]['domain_name'] }}
@{{ $domains[0]['domain_name'] }}

{{-- @each('view.name', $jobs, 'job') --}}


<label for="title">Post Title</label>

<input id="title" type="text" class="@error('title') is-invalid @enderror">

@error('title')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<script>
    var app = @json($domains, JSON_PRETTY_PRINT);
</script>