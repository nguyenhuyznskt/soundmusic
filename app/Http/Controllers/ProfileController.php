<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(string $username): View
    {
        $user = User::where('username', $username)->where('is_active', true)->firstOrFail();
        $songs = $user->songs()->published()->with('genre')->orderByDesc('play_count')->paginate(12);
        $playlists = $user->playlists()->where('visibility', 'public')->withCount('songs')->latest()->limit(6)->get();
        $following = auth()->check() && auth()->user()->following()->whereKey($user->id)->exists();
        return view('profiles.show', compact('user', 'songs', 'playlists', 'following'));
    }

    public function edit(): View { return view('profiles.edit', ['user' => auth()->user()]); }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.(config('music.max_image_mb') * 1024)],
        ]);
        $old = $user->avatar_path;
        $avatar = $request->file('avatar')?->store('avatars/'.$user->id, 'public');
        $user->update(['name' => $data['name'], 'bio' => $data['bio'] ?? null, 'avatar_path' => $avatar ?: $user->avatar_path]);
        if ($avatar && $old) Storage::disk('public')->delete($old);
        return redirect()->route('profiles.show', $user->username)->with('success', 'Đã cập nhật hồ sơ.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $request->user()->update(['password' => $data['password']]);
        return back()->with('success', 'Đã đổi mật khẩu.');
    }
}
