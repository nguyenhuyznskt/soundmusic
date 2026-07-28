<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function index(): View { return view('admin.genres', ['genres' => Genre::withCount('songs')->orderBy('name')->get()]); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:genres,name'], 'description' => ['nullable', 'string', 'max:1000']]);
        Genre::create(['name' => $data['name'], 'slug' => Str::slug($data['name']), 'description' => $data['description'] ?? null, 'is_active' => true]);
        return back()->with('success', 'Đã thêm thể loại.');
    }
    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:genres,name,'.$genre->id], 'description' => ['nullable', 'string', 'max:1000'], 'is_active' => ['required', 'boolean']]);
        $genre->update($data + ['slug' => Str::slug($data['name'])]);
        return back()->with('success', 'Đã cập nhật thể loại.');
    }
}
