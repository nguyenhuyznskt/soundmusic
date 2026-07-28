@extends('layouts.app')
@section('title','Đăng ký')
@section('content')
<div class="row justify-content-center py-lg-4"><div class="col-md-8 col-lg-6"><div class="glass-card p-4 p-lg-5"><h1 class="h3 fw-bold mb-2">Tạo tài khoản CloudMusic</h1><p class="text-muted mb-4">Chọn tài khoản người nghe hoặc nghệ sĩ.</p>
<form method="post" action="{{ route('register.store') }}">@csrf<div class="row g-3">
<div class="col-md-6"><label class="form-label">Họ tên</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
<div class="col-md-6"><label class="form-label">Tên đăng nhập</label><input class="form-control" name="username" value="{{ old('username') }}" required></div>
<div class="col-12"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email') }}" required></div>
<div class="col-md-6"><label class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" required></div>
<div class="col-md-6"><label class="form-label">Nhập lại mật khẩu</label><input class="form-control" type="password" name="password_confirmation" required></div>
<div class="col-12"><label class="form-label d-block">Loại tài khoản</label><div class="row g-2"><div class="col-6"><input class="btn-check" type="radio" name="account_type" value="listener" id="listener" {{ old('account_type','listener')==='listener'?'checked':'' }}><label class="btn btn-soft w-100 p-3" for="listener"><i class="bi bi-headphones d-block fs-3"></i>Người nghe</label></div><div class="col-6"><input class="btn-check" type="radio" name="account_type" value="artist" id="artist" {{ old('account_type')==='artist'?'checked':'' }}><label class="btn btn-soft w-100 p-3" for="artist"><i class="bi bi-mic d-block fs-3"></i>Nghệ sĩ</label></div></div></div>
<div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="terms" value="1" id="terms" required><label for="terms" class="form-check-label">Tôi đồng ý với điều khoản sử dụng và quy tắc cộng đồng.</label></div></div>
<div class="col-12"><button class="btn btn-accent btn-lg w-100">Tạo tài khoản</button></div></div></form><p class="text-center text-muted mt-4 mb-0">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p></div></div></div>
@endsection
