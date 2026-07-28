@extends('layouts.app')
@section('title','Album của tôi')
@section('content')
<div class="d-flex justify-content-between align-items-end mb-4"><div><h1 class="section-title">Album của tôi</h1><p class="text-muted mb-0">Nhóm các bài hát thành một bản phát hành.</p></div><a class="btn btn-accent" href="{{ route('albums.create') }}"><i class="bi bi-plus-lg me-1"></i>Tạo album</a></div>
<div class="row g-4">@forelse($albums as $album)<div class="col-6 col-md-4 col-xl-3"><a href="{{ route('albums.show',$album) }}"><div class="music-cover-wrap"><img src="{{ $album->cover_url }}" class="music-cover" alt="{{ $album->title }}"></div><div class="pt-3"><strong class="d-block text-truncate">{{ $album->title }}</strong><small class="text-muted">{{ $album->songs_count }} bài · {{ $album->visibility }}</small></div></a></div>@empty<div class="col-12"><div class="glass-card p-5 text-center"><i class="bi bi-disc fs-1 text-muted"></i><h3 class="mt-3">Chưa có album</h3><a class="btn btn-accent mt-2" href="{{ route('albums.create') }}">Tạo album đầu tiên</a></div></div>@endforelse</div><div class="mt-4">{{ $albums->links() }}</div>
@endsection
