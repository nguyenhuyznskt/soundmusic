@extends('layouts.app')
@section('title','Quên mật khẩu')
@section('content')
<div class="row justify-content-center py-lg-5"><div class="col-md-7 col-lg-5"><div class="glass-card p-4 p-lg-5"><h1 class="h3 fw-bold">Khôi phục mật khẩu</h1><p class="text-muted mb-4">Nhập email tài khoản. Trong môi trường local, liên kết khôi phục được ghi trong <code>storage/logs/laravel.log</code>.</p><form method="post" action="{{ route('password.email') }}">@csrf<div class="mb-3"><label class="form-label">Email</label><input class="form-control form-control-lg" type="email" name="email" value="{{ old('email') }}" required autofocus></div><button class="btn btn-accent btn-lg w-100">Gửi liên kết</button></form><a class="d-block text-center mt-4" href="{{ route('login') }}">Quay lại đăng nhập</a></div></div></div>
@endsection
