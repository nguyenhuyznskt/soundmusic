<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LibraryController extends Controller
{
    public function liked(): View
    {
        $songs = auth()->user()->likedSongs()->with(['user', 'genre'])->orderByDesc('song_likes.created_at')->paginate(16);
        return view('library.liked', compact('songs'));
    }

    public function history(): View
    {
        $histories = auth()->user()->listeningHistories()->with('song.user', 'song.genre')->orderByDesc('listened_at')->paginate(20);
        return view('library.history', compact('histories'));
    }
}
