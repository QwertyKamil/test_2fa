@extends('layouts.app')
@section('modals')
    <div class="modal fade" id="authy-modal">
        <div class='modal-dialog'>
            <div class="modal-content">
                <div class='modal-header'>
                    <h4 class='modal-title'>Please Authenticate</h4>
                </div>
                <div class='modal-body auth-ot'>
                    <div class='help-block'>
                        <i class="fa fa-spinner fa-pulse"></i> Waiting for OneTouch Approval, check your phone ...
                    </div>
                    <a class="btn btn-default" href="#" data-dismiss="modal" onclick="cancelLogin()">Cancel</a>
                </div>
                <div class='modal-body auth-token'>
                    <div class='help-block'>
                        <i class="fa fa-mobile"></i>Authy OneTouch not available?
                    </div>
                    <p id="token-info">You can also enter your Token</p>
                    <form id="authy-sms-form" class="form-horizontal" role="form" method="POST" action="/two-factor">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class='form-group'>
                            <label class="col-4 control-label text-left" for="token">Authy Token</label>
                            <div class='col-12'>
                                <input type="text" name="token" id="authy-token" value=""
                                       class="form-control" autocomplete="off"/>
                            </div>
                        </div>
                        <a class="btn btn-default" href="#" data-dismiss="modal" onclick="cancelLogin()">Cancel</a>
                        <input type="submit" name="commit" value="Verify2" class="btn btn-success"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="container">
        <div id="ajax-error" class="alert alert-danger hidden"></div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Login') }}</div>

                    <div class="card-body">
                        <form id="login-form" method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="email"
                                       class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email"
                                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           name="email" value="{{ old('email') }}" required autofocus>

                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password"
                                       class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                           name="password" required>

                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- <div class="form-group row">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Login') }}
                                    </button>
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{asset('js/sessions.js')}}" type="text/javascript"></script>
@endsection
