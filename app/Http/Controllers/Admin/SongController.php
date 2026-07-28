<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SongController extends Controller
{
    public function index(Request $request): View
    {
        $songs = Song::with(['user', 'genre'])->when($request->q, fn ($q, $term) => $q->search($term))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))->latest()->paginate(20)->withQueryString();
        return view('admin.songs', compact('songs'));
    }

    public function moderate(Request $request, Song $song): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:published,pending,rejected,blocked']]);
        $song->update(['status' => $data['status']]);
        return back()->with('success', 'Đã cập nhật trạng thái bài hát.');
    }
}
