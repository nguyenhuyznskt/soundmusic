<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $trending = Song::published()->with(['user', 'genre'])->orderByDesc('play_count')->limit(8)->get();
        $latest = Song::published()->with(['user', 'genre'])->latest()->limit(8)->get();
        $artists = User::whereIn('role', ['artist', 'admin'])->where('is_active', true)
            ->withCount(['followers', 'songs' => fn ($q) => $q->published()])
            ->orderByDesc('followers_count')->limit(6)->get();
        $playlists = Playlist::where('visibility', 'public')->with(['user', 'songs.user'])->withCount('songs')->latest()->limit(6)->get();
        $genres = Genre::where('is_active', true)->withCount(['songs' => fn ($q) => $q->published()])->orderByDesc('songs_count')->limit(8)->get();

        return view('home', compact('trending', 'latest', 'artists', 'playlists', 'genres'));
    }

    public function search(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'sort' => ['nullable', 'in:popular,latest,title'],
        ]);

        $query = Song::published()->with(['user', 'genre'])->search($data['q'] ?? null);
        if (!empty($data['genre'])) $query->where('genre_id', $data['genre']);
        match ($data['sort'] ?? 'popular') {
            'latest' => $query->latest(),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('play_count'),
        };

        $songs = $query->paginate(16)->withQueryString();
        $artists = collect();
        if (!empty($data['q'])) {
            $term = $data['q'];
            $artists = User::whereIn('role', ['artist', 'admin'])->where('is_active', true)
                ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('username', 'like', "%{$term}%"))
                ->withCount('followers')->limit(8)->get();
        }
        $genres = Genre::where('is_active', true)->orderBy('name')->get();

        return view('search', compact('songs', 'artists', 'genres'));
    }
}
