<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaylistRequest;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    public function index(): View
    {
        $playlists = auth()->user()->playlists()->withCount('songs')->latest()->paginate(12);
        return view('playlists.index', compact('playlists'));
    }

    public function show(Playlist $playlist): View
    {
        abort_unless($playlist->visibility === 'public' || auth()->id() === $playlist->user_id || auth()->user()?->isAdmin(), 404);
        $playlist->load(['user', 'songs.user', 'songs.genre']);
        return view('playlists.show', compact('playlist'));
    }

    public function create(): View { return view('playlists.form', ['playlist' => new Playlist(['visibility' => 'public']), 'editing' => false]); }

    public function store(PlaylistRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $playlist = auth()->user()->playlists()->create([
            'name' => $data['name'], 'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null, 'visibility' => $data['visibility'],
            'cover_path' => $request->file('cover')?->store('playlist-covers/'.auth()->id(), 'public'),
        ]);
        return redirect()->route('playlists.show', $playlist)->with('success', 'Đã tạo playlist.');
    }

    public function edit(Playlist $playlist): View
    {
        $this->authorizePlaylist($playlist);
        return view('playlists.form', compact('playlist') + ['editing' => true]);
    }

    public function update(PlaylistRequest $request, Playlist $playlist): RedirectResponse
    {
        $this->authorizePlaylist($playlist);
        $data = $request->validated();
        $oldCover = $playlist->cover_path;
        $newCover = $request->file('cover')?->store('playlist-covers/'.auth()->id(), 'public');
        $playlist->update([
            'name' => $data['name'],
            'slug' => $playlist->name === $data['name'] ? $playlist->slug : $this->uniqueSlug($data['name'], $playlist->id),
            'description' => $data['description'] ?? null, 'visibility' => $data['visibility'],
            'cover_path' => $newCover ?: $playlist->cover_path,
        ]);
        if ($newCover && $oldCover) Storage::disk('public')->delete($oldCover);
        return redirect()->route('playlists.show', $playlist)->with('success', 'Đã cập nhật playlist.');
    }

    public function destroy(Playlist $playlist): RedirectResponse
    {
        $this->authorizePlaylist($playlist);
        $cover = $playlist->cover_path; $playlist->delete();
        if ($cover) Storage::disk('public')->delete($cover);
        return redirect()->route('playlists.index')->with('success', 'Đã xóa playlist.');
    }

    public function addSong(Request $request, Playlist $playlist, Song $song): RedirectResponse|JsonResponse
    {
        $this->authorizePlaylist($playlist);
        abort_unless($song->status === 'published', 404);
        $position = ((int) $playlist->songs()->max('position')) + 1;
        $playlist->songs()->syncWithoutDetaching([$song->id => ['position' => $position]]);
        if ($request->expectsJson()) return response()->json(['added' => true]);
        return back()->with('success', 'Đã thêm bài hát vào playlist.');
    }

    public function removeSong(Playlist $playlist, Song $song): RedirectResponse
    {
        $this->authorizePlaylist($playlist);
        $playlist->songs()->detach($song->id);
        $this->normalizePositions($playlist);
        return back()->with('success', 'Đã xóa bài hát khỏi playlist.');
    }

    public function reorder(Request $request, Playlist $playlist): JsonResponse
    {
        $this->authorizePlaylist($playlist);
        $data = $request->validate(['song_ids' => ['required', 'array'], 'song_ids.*' => ['integer', 'distinct']]);
        $allowed = $playlist->songs()->pluck('songs.id')->sort()->values()->all();
        $given = collect($data['song_ids'])->sort()->values()->all();
        abort_unless($allowed === $given, 422, 'Danh sách bài hát không hợp lệ.');
        foreach ($data['song_ids'] as $index => $songId) $playlist->songs()->updateExistingPivot($songId, ['position' => $index + 1]);
        return response()->json(['saved' => true]);
    }

    private function authorizePlaylist(Playlist $playlist): void { abort_unless(auth()->user()->can('update', $playlist), 403); }
    private function normalizePositions(Playlist $playlist): void
    {
        foreach ($playlist->songs()->orderByPivot('position')->pluck('songs.id') as $index => $id) {
            $playlist->songs()->updateExistingPivot($id, ['position' => $index + 1]);
        }
    }
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'playlist'; $slug = $base; $i = 2;
        while (Playlist::where('user_id', auth()->id())->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) $slug = $base.'-'.$i++;
        return $slug;
    }
}
