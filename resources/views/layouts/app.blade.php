<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CloudMusic') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@yield('bodyClass')">
<nav class="navbar navbar-expand-lg sticky-top py-2" id="appNavbar">
    <div class="container-fluid px-lg-4">
        <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <span class="brand-mark"><i class="bi bi-soundwave"></i></span><span>CloudMusic</span>
        </a>
        <form class="d-flex flex-grow-1 mx-lg-5 desktop-search" action="{{ route('search') }}" method="get" style="max-width:680px">
            <div class="input-group">
                <span class="input-group-text search-box border-end-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                <input class="form-control search-box border-start-0 ps-0" name="q" value="{{ request('q') }}" placeholder="Tìm bài hát, nghệ sĩ, thể loại...">
            </div>
        </form>
        <div class="d-flex align-items-center gap-2">
            @auth
                @if(auth()->user()->isArtist())
                    <a href="{{ route('songs.create') }}" class="btn btn-accent btn-sm px-3"><i class="bi bi-cloud-arrow-up me-1"></i> Tải nhạc</a>
                @endif
                <div class="dropdown">
                    <button class="btn btn-soft btn-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <span class="avatar" style="width:30px;height:30px;font-size:.72rem">
                            @if(auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" class="w-100 h-100 rounded-circle object-fit-cover" alt="">@else{{ auth()->user()->initials }}@endif
                        </span>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('profiles.show', auth()->user()->username) }}"><i class="bi bi-person me-2"></i>Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="{{ route('profiles.edit') }}"><i class="bi bi-gear me-2"></i>Cài đặt</a></li>
                        @if(auth()->user()->isAdmin())<li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Quản trị</a></li>@endif
                        <li><hr class="dropdown-divider border-soft"></li>
                        <li><form method="post" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button></form></li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-soft btn-sm px-3">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn btn-accent btn-sm px-3">Đăng ký</a>
            @endauth
        </div>
    </div>
</nav>

<aside class="app-sidebar" id="appSidebar">
    <div class="small text-uppercase text-muted fw-bold px-3 mb-2">Khám phá</div>
    <nav class="nav flex-column mb-4">
        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-house-door me-3"></i>Trang chủ</a>
        <a class="nav-link {{ request()->routeIs('search') ? 'active' : '' }}" href="{{ route('search') }}"><i class="bi bi-compass me-3"></i>Khám phá</a>
    </nav>
    @auth
        <div class="small text-uppercase text-muted fw-bold px-3 mb-2">Thư viện</div>
        <nav class="nav flex-column mb-4">
            <a class="nav-link {{ request()->routeIs('library.liked') ? 'active' : '' }}" href="{{ route('library.liked') }}"><i class="bi bi-heart me-3"></i>Đã thích</a>
            <a class="nav-link {{ request()->routeIs('library.history') ? 'active' : '' }}" href="{{ route('library.history') }}"><i class="bi bi-clock-history me-3"></i>Lịch sử nghe</a>
            <a class="nav-link {{ request()->routeIs('playlists.*') ? 'active' : '' }}" href="{{ route('playlists.index') }}"><i class="bi bi-music-note-list me-3"></i>Playlist</a>
        </nav>
        @if(auth()->user()->isArtist())
            <div class="small text-uppercase text-muted fw-bold px-3 mb-2">Studio</div>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('songs.mine') ? 'active' : '' }}" href="{{ route('songs.mine') }}"><i class="bi bi-vinyl me-3"></i>Kho nhạc</a>
                <a class="nav-link {{ request()->routeIs('albums.*') ? 'active' : '' }}" href="{{ route('albums.index') }}"><i class="bi bi-disc me-3"></i>Album</a>
                <a class="nav-link {{ request()->routeIs('songs.create') ? 'active' : '' }}" href="{{ route('songs.create') }}"><i class="bi bi-cloud-arrow-up me-3"></i>Tải bài hát</a>
            </nav>
        @endif
    @else
        <div class="glass-card p-3 mt-4">
            <div class="fw-bold mb-1">Tạo thư viện riêng</div>
            <p class="small text-muted mb-3">Đăng nhập để thích nhạc, tạo playlist và theo dõi nghệ sĩ.</p>
            <a href="{{ route('register') }}" class="btn btn-accent btn-sm w-100">Bắt đầu miễn phí</a>
        </div>
    @endauth
</aside>

<main class="app-main" id="appMain">
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><strong>Vui lòng kiểm tra lại:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>

@include('partials.player')
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastHost" style="z-index:2000"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
