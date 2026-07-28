@extends('layouts.app')
@section('title','Đặt lại mật khẩu')
@section('content')
<div class="row justify-content-center py-lg-5"><div class="col-md-7 col-lg-5"><div class="glass-card p-4 p-lg-5"><h1 class="h3 fw-bold mb-4">Đặt mật khẩu mới</h1><form method="post" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email',$email) }}" required></div><div class="mb-3"><label class="form-label">Mật khẩu mới</label><input class="form-control" type="password" name="password" required></div><div class="mb-4"><label class="form-label">Nhập lại mật khẩu</label><input class="form-control" type="password" name="password_confirmation" required></div><button class="btn btn-accent btn-lg w-100">Cập nhật mật khẩu</button></form></div></div></div>
@endsection
