@extends('layouts.app')
@section('title','Đăng nhập')
@section('content')
<div class="row justify-content-center py-lg-5"><div class="col-md-7 col-lg-5 col-xl-4"><div class="glass-card p-4 p-lg-5"><div class="text-center mb-4"><span class="brand-mark mx-auto mb-3"><i class="bi bi-soundwave"></i></span><h1 class="h3 fw-bold">Chào mừng trở lại</h1><p class="text-muted">Đăng nhập để tiếp tục nghe nhạc.</p></div>
<form method="post" action="{{ route('login.store') }}">@csrf
<div class="mb-3"><label class="form-label">Email hoặc tên đăng nhập</label><input class="form-control form-control-lg" name="login" value="{{ old('login') }}" required autofocus></div>
<div class="mb-3"><label class="form-label">Mật khẩu</label><input class="form-control form-control-lg" type="password" name="password" required></div>
<div class="d-flex justify-content-between align-items-center mb-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="remember" value="1" id="remember"><label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label></div><a class="small" href="{{ route('password.request') }}">Quên mật khẩu?</a></div>
<button class="btn btn-accent btn-lg w-100">Đăng nhập</button></form><p class="text-center text-muted mt-4 mb-0">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p></div></div></div>
@endsection
