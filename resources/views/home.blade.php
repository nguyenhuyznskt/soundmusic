@extends('layouts.app')
@section('title','Nghe nhạc và chia sẻ âm thanh')
@section('content')
<section class="hero mb-5">
    <span class="badge rounded-pill text-bg-light mb-3">Nền tảng dành cho âm thanh độc lập</span>
    <h1 class="fw-black mb-3">Khám phá âm thanh mới. Chia sẻ chất riêng của bạn.</h1>
    <p class="lead text-white-50 mb-4" style="max-width:680px">Nghe nhạc, tạo playlist, theo dõi nghệ sĩ và đăng tải sản phẩm âm thanh trong một trải nghiệm liền mạch.</p>
    <div class="d-flex flex-wrap gap-2"><a href="{{ route('search') }}" class="btn btn-accent btn-lg px-4">Khám phá ngay</a>@guest<a href="{{ route('register') }}" class="btn btn-soft btn-lg px-4">Tạo tài khoản</a>@endguest</div>
</section>

@if($trending->isNotEmpty())
<section class="mb-5"><div class="d-flex justify-content-between align-items-end mb-3"><div><h2 class="section-title mb-1">Đang thịnh hành</h2><p class="text-muted mb-0">Những bài hát được nghe nhiều nhất.</p></div><a href="{{ route('search', ['sort'=>'popular']) }}" class="small">Xem tất cả <i class="bi bi-arrow-right"></i></a></div>
<div class="row g-4 music-grid" data-song-collection>@foreach($trending as $song)<div class="col-6 col-md-4 col-xl-3 col-xxl-2">@include('partials.song-card')</div>@endforeach</div></section>
@endif

@if($latest->isNotEmpty())
<section class="mb-5"><div class="d-flex justify-content-between align-items-end mb-3"><div><h2 class="section-title mb-1">Mới phát hành</h2><p class="text-muted mb-0">Âm thanh mới nhất từ cộng đồng.</p></div><a href="{{ route('search', ['sort'=>'latest']) }}" class="small">Xem tất cả <i class="bi bi-arrow-right"></i></a></div>
<div class="row g-4 music-grid" data-song-collection>@foreach($latest as $song)<div class="col-6 col-md-4 col-xl-3 col-xxl-2">@include('partials.song-card')</div>@endforeach</div></section>
@endif

<div class="row g-4 mb-5">
    <div class="col-xl-7"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="section-title mb-0">Nghệ sĩ nổi bật</h2></div><div class="row g-3">@forelse($artists as $artist)<div class="col-md-6"><a href="{{ route('profiles.show',$artist->username) }}" class="glass-card artist-card p-3 d-flex align-items-center gap-3 h-100"><span class="avatar">@if($artist->avatar_url)<img src="{{ $artist->avatar_url }}" class="w-100 h-100 rounded-circle object-fit-cover">@else{{ $artist->initials }}@endif</span><span class="min-w-0"><strong class="d-block text-truncate">{{ $artist->name }}</strong><small class="text-muted">{{ number_format($artist->followers_count) }} người theo dõi · {{ $artist->songs_count }} bài</small></span></a></div>@empty<p class="text-muted">Chưa có nghệ sĩ.</p>@endforelse</div></div>
    <div class="col-xl-5"><h2 class="section-title mb-3">Thể loại</h2><div class="glass-card p-4 d-flex flex-wrap gap-2">@foreach($genres as $genre)<a class="genre-chip" href="{{ route('search',['genre'=>$genre->id]) }}">{{ $genre->name }} <span class="ms-2 small">{{ $genre->songs_count }}</span></a>@endforeach</div></div>
</div>

@if($playlists->isNotEmpty())
<section><h2 class="section-title mb-3">Playlist cộng đồng</h2><div class="row g-4">@foreach($playlists as $playlist)<div class="col-6 col-md-4 col-xl-2"><a href="{{ route('playlists.show',$playlist) }}"><div class="music-cover-wrap"><img src="{{ $playlist->cover_url }}" class="music-cover" alt="{{ $playlist->name }}"></div><div class="pt-3"><strong class="d-block text-truncate">{{ $playlist->name }}</strong><small class="text-muted">{{ $playlist->songs_count }} bài · {{ $playlist->user->name }}</small></div></a></div>@endforeach</div></section>
@endif
@endsection
