<article class="music-card">
    <div class="music-cover-wrap">
        <img class="music-cover" src="{{ $song->cover_url }}" alt="{{ $song->title }}" loading="lazy">
        <button class="play-fab" data-play-song data-song='@json($song->toPlayerPayload())' aria-label="Phát {{ $song->title }}"><i class="bi bi-play-fill fs-4"></i></button>
    </div>
    <div class="pt-3">
        <a href="{{ route('songs.show', $song) }}" class="music-title d-block">{{ $song->title }}</a>
        <a href="{{ route('profiles.show', $song->user->username) }}" class="music-meta d-block">{{ $song->user->name }}</a>
        <div class="d-flex align-items-center gap-3 mt-1 small text-muted"><span><i class="bi bi-play-fill"></i> {{ number_format($song->play_count) }}</span><span><i class="bi bi-heart"></i> {{ number_format($song->like_count) }}</span></div>
    </div>
</article>
