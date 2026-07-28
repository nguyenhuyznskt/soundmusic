<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MusicFlowTest extends TestCase
{
    use RefreshDatabase;

    private function artist(): User
    {
        return User::create([
            'name' => 'Artist', 'username' => 'artist', 'email' => 'artist@example.test',
            'password' => 'Password123!', 'role' => 'artist', 'is_active' => true,
        ]);
    }

    private function publishedSong(User $artist): Song
    {
        Storage::disk('local')->put('music/test.wav', str_repeat('A', 2048));
        $genre = Genre::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true]);
        return Song::create([
            'user_id' => $artist->id, 'genre_id' => $genre->id, 'title' => 'Test Song', 'slug' => 'test-song',
            'audio_path' => 'music/test.wav', 'audio_mime' => 'audio/wav', 'duration_seconds' => 20,
            'visibility' => 'public', 'status' => 'published',
        ]);
    }

    public function test_stream_supports_range_requests(): void
    {
        $song = $this->publishedSong($this->artist());
        $response = $this->withHeader('Range', 'bytes=0-99')->get(route('songs.stream', $song));
        $response->assertStatus(206)->assertHeader('Accept-Ranges', 'bytes')->assertHeader('Content-Length', '100');
    }

    public function test_like_is_unique_and_counter_is_updated(): void
    {
        $song = $this->publishedSong($this->artist());
        $listener = User::create([
            'name' => 'Listener', 'username' => 'listener', 'email' => 'listener@example.test',
            'password' => 'Password123!', 'role' => 'listener', 'is_active' => true,
        ]);
        $this->actingAs($listener)->post(route('songs.like', $song))->assertRedirect();
        $this->assertDatabaseCount('song_likes', 1);
        $this->assertSame(1, $song->fresh()->like_count);
        $this->actingAs($listener)->post(route('songs.like', $song))->assertRedirect();
        $this->assertDatabaseCount('song_likes', 0);
        $this->assertSame(0, $song->fresh()->like_count);
    }

    public function test_owner_can_add_song_to_playlist_only_once(): void
    {
        $artist = $this->artist();
        $song = $this->publishedSong($artist);
        $playlist = Playlist::create(['user_id' => $artist->id, 'name' => 'List', 'slug' => 'list', 'visibility' => 'public']);
        $this->actingAs($artist)->post(route('playlists.songs.add', [$playlist, $song]))->assertRedirect();
        $this->actingAs($artist)->post(route('playlists.songs.add', [$playlist, $song]))->assertRedirect();
        $this->assertDatabaseCount('playlist_song', 1);
    }
}
