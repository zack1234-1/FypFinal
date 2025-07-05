@extends('front-end.layout')
@section('title')
    {{ get_label('login', 'Login') }}
@endsection
@section('content')
    <div class="min-vh-100 d-flex align-items-center bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <h2 class="mb-0 fw-bold">
                                <i class="fas fa-user-circle me-2"></i>
                                {{ get_label('login', 'Login') }}
                            </h2>
                            <p class="mb-0 mt-2 opacity-75">
                                {{ get_label('login_register_subheading', 'Access your account or create a new one to start managing your projects.') }}
                            </p>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <form id="formAuthentication" class="form-submit-event"
                                action="{{ route('users.authenticate') }}" method="POST">
                                <input type="hidden" name="redirect_url" value="{{ route('home.index') }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-1"></i>
                                        {{ get_label('email', 'Email') }} 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="email" name="email"
                                            placeholder="{{ get_label('enter_your_email', 'Please enter your email') }}"
                                            value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? 'superadmin@gmail.com' : '' ?>"
                                            autofocus />
                                    </div>
                                    <p class="text-danger error-message mt-1 small"></p>
                                    @error('email')
                                        <p class="text-danger mt-1 small">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-semibold mb-0" for="password">
                                            <i class="fas fa-lock me-1"></i>
                                            {{ get_label('password', 'Password') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" id="password" class="form-control border-start-0 border-end-0 ps-0" name="password"
                                            placeholder="{{ get_label('enter_your_password', 'Please enter your password') }}"
                                            value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? '12345678' : '' ?>"
                                            aria-describedby="password" />
                                        <span class="input-group-text bg-light border-start-0 cursor-pointer" id="eyeicon">
                                            <i class="far fa-eye-slash text-muted"></i>
                                        </span>
                                    </div>
                                    <p class="text-danger error-message mt-1 small"></p>
                                    @error('password')
                                        <p class="text-danger mt-1 small">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="d-grid mb-4">
                                    <button class="btn btn-primary btn-lg fw-semibold" type="submit" id="loginBtn">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        {{ get_label('login', 'Login') }}
                                    </button>
                                </div>

                                @if (config('constants.ALLOW_MODIFICATION') === 0)
                                    <div class="border-top pt-4">
                                        <h6 class="text-center text-muted mb-3">Quick Login Options</h6>
                                        <div class="row g-2">
                                            <div class="col-12 col-sm-6">
                                                <button class="btn btn-outline-danger w-100 superadmin-login" type="button">
                                                    <i class="fas fa-crown me-1"></i>
                                                    <span class="d-none d-sm-inline">{{ get_label('login_as_superadmin', 'Login As Super Admin') }}</span>
                                                    <span class="d-sm-none">Super Admin</span>
                                                </button>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <button class="btn btn-outline-success w-100 admin-login" type="button">
                                                    <i class="fas fa-user-shield me-1"></i>
                                                    <span class="d-none d-sm-inline">{{ get_label('login_as_admin', 'Login As Admin') }}</span>
                                                    <span class="d-sm-none">Admin</span>
                                                </button>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <button class="btn btn-outline-info w-100 member-login" type="button">
                                                    <i class="fas fa-users me-1"></i>
                                                    <span class="d-none d-sm-inline">{{ get_label('login_as_team_member', 'Login As Team Member') }}</span>
                                                    <span class="d-sm-none">Team Member</span>
                                                </button>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <button class="btn btn-outline-warning w-100 client-login" type="button">
                                                    <i class="fas fa-user-tie me-1"></i>
                                                    <span class="d-none d-sm-inline">{{ get_label('login_as_client', 'Login As Client') }}</span>
                                                    <span class="d-sm-none">Client</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                        <div class="card-footer bg-light text-center py-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Your information is secure and encrypted
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection