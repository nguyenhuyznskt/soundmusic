@extends('layouts.app')
@section('title','Playlist của tôi')
@section('content')
<div class="d-flex justify-content-between align-items-end mb-4"><div><h1 class="section-title">Playlist của tôi</h1><p class="text-muted mb-0">Sắp xếp âm nhạc theo tâm trạng của bạn.</p></div><a class="btn btn-accent" href="{{ route('playlists.create') }}"><i class="bi bi-plus-lg me-1"></i>Tạo playlist</a></div>
<div class="row g-4">@forelse($playlists as $playlist)<div class="col-6 col-md-4 col-xl-3"><a href="{{ route('playlists.show',$playlist) }}"><div class="music-cover-wrap"><img src="{{ $playlist->cover_url }}" class="music-cover"></div><div class="pt-3"><strong class="d-block text-truncate">{{ $playlist->name }}</strong><small class="text-muted">{{ $playlist->songs_count }} bài · {{ $playlist->visibility }}</small></div></a></div>@empty<div class="col-12"><div class="glass-card text-center p-5"><i class="bi bi-music-note-list fs-1 text-muted"></i><h3 class="mt-3">Chưa có playlist</h3><a class="btn btn-accent mt-2" href="{{ route('playlists.create') }}">Tạo playlist đầu tiên</a></div></div>@endforelse</div><div class="mt-4">{{ $playlists->links() }}</div>
@endsection
