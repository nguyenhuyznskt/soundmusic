<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class AlbumController extends Controller
{
    public function show(Album $album): View
    {
        abort_unless($album->visibility === 'public' || auth()->id() === $album->user_id || auth()->user()?->isAdmin(), 404);
        $album->load(['user', 'songs' => fn ($q) => $q->where(function ($inner) use ($album) {
            $inner->where('status', 'published')->where('visibility', 'public');
            if (auth()->id() === $album->user_id || auth()->user()?->isAdmin()) {
                $inner->orWhere('user_id', $album->user_id);
            }
        })->with(['user', 'genre'])]);
        return view('albums.show', compact('album'));
    }

    public function index(): View
    {
        $albums = auth()->user()->albums()->withCount('songs')->latest()->paginate(12);
        return view('albums.index', compact('albums'));
    }

    public function create(): View
    {
        return view('albums.form', ['album' => new Album(['visibility' => 'public']), 'editing' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $album = auth()->user()->albums()->create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'description' => $data['description'] ?? null,
            'release_date' => $data['release_date'] ?? null,
            'visibility' => $data['visibility'],
            'cover_path' => $request->file('cover')?->store('album-covers/'.auth()->id(), 'public'),
        ]);
        return redirect()->route('albums.show', $album)->with('success', 'Đã tạo album.');
    }

    public function edit(Album $album): View
    {
        $this->authorizeAlbum($album);
        return view('albums.form', compact('album') + ['editing' => true]);
    }

    public function update(Request $request, Album $album): RedirectResponse
    {
        $this->authorizeAlbum($album);
        $data = $this->validateData($request);
        $oldCover = $album->cover_path;
        $newCover = $request->file('cover')?->store('album-covers/'.auth()->id(), 'public');
        $album->update([
            'title' => $data['title'],
            'slug' => $album->title === $data['title'] ? $album->slug : $this->uniqueSlug($data['title'], $album->id),
            'description' => $data['description'] ?? null,
            'release_date' => $data['release_date'] ?? null,
            'visibility' => $data['visibility'],
            'cover_path' => $newCover ?: $album->cover_path,
        ]);
        if ($newCover && $oldCover && !str_starts_with($oldCover, 'demo-covers/')) Storage::disk('public')->delete($oldCover);
        return redirect()->route('albums.show', $album)->with('success', 'Đã cập nhật album.');
    }

    public function destroy(Album $album): RedirectResponse
    {
        $this->authorizeAlbum($album);
        $cover = $album->cover_path;
        $album->delete();
        if ($cover && !str_starts_with($cover, 'demo-covers/')) Storage::disk('public')->delete($cover);
        return redirect()->route('albums.index')->with('success', 'Đã xóa album; bài hát vẫn được giữ lại.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'release_date' => ['nullable', 'date'],
            'visibility' => ['required', 'in:public,private'],
            'cover' => ['nullable', File::image()->max(config('music.max_image_mb') * 1024), 'mimes:jpg,jpeg,png,webp'],
        ]);
    }

    private function authorizeAlbum(Album $album): void { abort_unless(auth()->user()->can('update', $album), 403); }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'album'; $slug = $base; $i = 2;
        while (Album::where('user_id', auth()->id())->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
