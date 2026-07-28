<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\ListeningHistory;
use App\Models\PlayEvent;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InteractionController extends Controller
{
    public function toggleLike(Request $request, Song $song): JsonResponse|RedirectResponse
    {
        abort_unless($song->status === 'published', 404);
        $exists = $request->user()->likedSongs()->whereKey($song->id)->exists();
        DB::transaction(function () use ($request, $song, $exists) {
            if ($exists) {
                $request->user()->likedSongs()->detach($song->id);
                $song->whereKey($song->id)->where('like_count', '>', 0)->decrement('like_count');
            } else {
                $request->user()->likedSongs()->attach($song->id);
                $song->increment('like_count');
            }
        });
        $song->refresh();
        if ($request->expectsJson()) return response()->json(['liked' => !$exists, 'like_count' => $song->like_count]);
        return back()->with('success', $exists ? 'Đã bỏ thích.' : 'Đã thêm vào bài hát yêu thích.');
    }

    public function comment(Request $request, Song $song): RedirectResponse
    {
        abort_unless($song->status === 'published', 404);
        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);
        if (!empty($data['parent_id'])) {
            abort_unless(Comment::whereKey($data['parent_id'])->where('song_id', $song->id)->exists(), 422);
        }
        DB::transaction(function () use ($request, $song, $data) {
            Comment::create(['user_id' => $request->user()->id, 'song_id' => $song->id, 'parent_id' => $data['parent_id'] ?? null, 'content' => $data['content']]);
            $song->increment('comment_count');
        });
        return back()->with('success', 'Đã đăng bình luận.');
    }

    public function deleteComment(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()->id === $comment->user_id || $request->user()->isAdmin(), 403);
        DB::transaction(function () use ($comment) {
            $count = 1 + Comment::where('parent_id', $comment->id)->count();
            $song = Song::lockForUpdate()->find($comment->song_id);
            $comment->delete();
            if ($song) {
                $song->update(['comment_count' => max(0, $song->comment_count - $count)]);
            }
        });
        return back()->with('success', 'Đã xóa bình luận.');
    }

    public function toggleFollow(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_if($request->user()->is($user), 422, 'Không thể tự theo dõi chính mình.');
        abort_unless($user->isArtist() && $user->is_active, 404);
        $exists = $request->user()->following()->whereKey($user->id)->exists();
        $exists ? $request->user()->following()->detach($user->id) : $request->user()->following()->attach($user->id);
        $count = $user->followers()->count();
        if ($request->expectsJson()) return response()->json(['following' => !$exists, 'followers_count' => $count]);
        return back()->with('success', $exists ? 'Đã bỏ theo dõi.' : 'Đã theo dõi nghệ sĩ.');
    }

    public function played(Request $request, Song $song): JsonResponse
    {
        abort_unless($song->status === 'published' && $song->visibility === 'public', 404);
        $data = $request->validate(['position' => ['nullable', 'integer', 'min:0', 'max:7200']]);
        $userId = $request->user()?->id;
        $sessionKey = $request->session()->getId();
        $recent = PlayEvent::where('song_id', $song->id)->where('played_at', '>=', now()->subMinutes(30))
            ->where(function ($q) use ($userId, $sessionKey) {
                $userId ? $q->where('user_id', $userId) : $q->whereNull('user_id')->where('session_key', $sessionKey);
            })->exists();

        if (!$recent) {
            DB::transaction(function () use ($request, $song, $userId, $sessionKey) {
                PlayEvent::create([
                    'song_id' => $song->id,
                    'user_id' => $userId,
                    'session_key' => $sessionKey,
                    'ip_hash' => hash('sha256', (string) $request->ip().config('app.key')),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                    'played_at' => now(),
                ]);
                $song->increment('play_count');
            });
        }

        if ($userId) {
            ListeningHistory::updateOrCreate(
                ['user_id' => $userId, 'song_id' => $song->id],
                ['last_position_seconds' => (int) ($data['position'] ?? 0), 'listened_at' => now()]
            );
        }

        return response()->json(['counted' => !$recent, 'play_count' => $song->fresh()->play_count]);
    }
}
