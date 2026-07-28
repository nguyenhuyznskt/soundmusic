<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Models\Genre;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SongController extends Controller
{
    public function show(Song $song): View
    {
        $canAccess = $song->status === 'published' && $song->visibility === 'public'
            || auth()->check() && (auth()->id() === $song->user_id || auth()->user()->isAdmin());
        abort_unless($canAccess, 404);

        $song->load(['user', 'genre', 'album', 'comments.user', 'comments.replies.user']);
        $related = Song::published()->whereKeyNot($song->id)
            ->where(fn ($q) => $q->where('genre_id', $song->genre_id)->orWhere('user_id', $song->user_id))
            ->with(['user', 'genre'])->orderByDesc('play_count')->limit(6)->get();
        $liked = auth()->check() && auth()->user()->likedSongs()->whereKey($song->id)->exists();
        $playlists = auth()->check() ? auth()->user()->playlists()->orderBy('name')->get() : collect();

        return view('songs.show', compact('song', 'related', 'liked', 'playlists'));
    }

    public function mine(): View
    {
        $songs = auth()->user()->songs()->with(['genre', 'album'])->latest()->paginate(15);
        return view('songs.mine', compact('songs'));
    }

    public function create(): View
    {
        return view('songs.form', [
            'song' => new Song(['visibility' => 'public']),
            'genres' => Genre::where('is_active', true)->orderBy('name')->get(),
            'albums' => auth()->user()->albums()->orderBy('title')->get(),
            'editing' => false,
        ]);
    }

    public function store(StoreSongRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $audioPath = $request->file('audio')->store('music/'.auth()->id(), 'local');
        $coverPath = $request->file('cover')?->store('covers/'.auth()->id(), 'public');

        try {
            $song = DB::transaction(function () use ($data, $audioPath, $coverPath, $request) {
                return Song::create([
                    'user_id' => auth()->id(),
                    'genre_id' => $data['genre_id'] ?? null,
                    'album_id' => $data['album_id'] ?? null,
                    'title' => $data['title'],
                    'slug' => $this->uniqueSlug($data['title']),
                    'description' => $data['description'] ?? null,
                    'audio_path' => $audioPath,
                    'audio_mime' => $request->file('audio')->getMimeType() ?: 'application/octet-stream',
                    'cover_path' => $coverPath,
                    'duration_seconds' => $data['duration_seconds'],
                    'track_number' => $data['track_number'] ?? null,
                    'release_date' => $data['release_date'] ?? null,
                    'visibility' => $data['visibility'],
                    'status' => auth()->user()->isAdmin() ? 'published' : 'pending',
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($audioPath);
            if ($coverPath) Storage::disk('public')->delete($coverPath);
            throw $e;
        }

        return redirect()->route('songs.show', $song)->with('success', 'Đã tải bài hát lên. Bài hát đang chờ quản trị viên duyệt.');
    }

    public function edit(Song $song): View
    {
        $this->authorizeSong($song);
        return view('songs.form', [
            'song' => $song,
            'genres' => Genre::where('is_active', true)->orderBy('name')->get(),
            'albums' => auth()->user()->albums()->orderBy('title')->get(),
            'editing' => true,
        ]);
    }

    public function update(UpdateSongRequest $request, Song $song): RedirectResponse
    {
        $this->authorizeSong($song);
        $data = $request->validated();
        $oldAudio = $song->audio_path;
        $oldCover = $song->cover_path;
        $newAudio = $request->file('audio')?->store('music/'.auth()->id(), 'local');
        $newCover = $request->file('cover')?->store('covers/'.auth()->id(), 'public');

        try {
            DB::transaction(function () use ($song, $data, $newAudio, $newCover, $request) {
                $song->update([
                    'genre_id' => $data['genre_id'] ?? null,
                    'album_id' => $data['album_id'] ?? null,
                    'title' => $data['title'],
                    'slug' => $song->title === $data['title'] ? $song->slug : $this->uniqueSlug($data['title'], $song->id),
                    'description' => $data['description'] ?? null,
                    'audio_path' => $newAudio ?: $song->audio_path,
                    'audio_mime' => $newAudio ? ($request->file('audio')->getMimeType() ?: 'application/octet-stream') : $song->audio_mime,
                    'cover_path' => $newCover ?: $song->cover_path,
                    'duration_seconds' => $data['duration_seconds'] ?? $song->duration_seconds,
                    'track_number' => $data['track_number'] ?? null,
                    'release_date' => $data['release_date'] ?? null,
                    'visibility' => $data['visibility'],
                    'status' => auth()->user()->isAdmin() ? $song->status : 'pending',
                ]);
            });
        } catch (\Throwable $e) {
            if ($newAudio) Storage::disk('local')->delete($newAudio);
            if ($newCover) Storage::disk('public')->delete($newCover);
            throw $e;
        }

        if ($newAudio && !str_starts_with($oldAudio, 'music/demo/')) Storage::disk('local')->delete($oldAudio);
        if ($newCover && $oldCover && !str_starts_with($oldCover, 'demo-covers/')) Storage::disk('public')->delete($oldCover);

        return redirect()->route('songs.show', $song)->with('success', 'Đã cập nhật bài hát.');
    }

    public function destroy(Song $song): RedirectResponse
    {
        $this->authorizeSong($song);
        $audio = $song->audio_path; $cover = $song->cover_path;
        $song->delete();
        if (!str_starts_with($audio, 'music/demo/')) Storage::disk('local')->delete($audio);
        if ($cover && !str_starts_with($cover, 'demo-covers/')) Storage::disk('public')->delete($cover);
        return redirect()->route('songs.mine')->with('success', 'Đã xóa bài hát.');
    }

    private function authorizeSong(Song $song): void { abort_unless(auth()->user()->can('update', $song), 403); }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'track'; $slug = $base; $counter = 2;
        while (Song::where('user_id', auth()->id())->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }
        return $slug;
    }
}
