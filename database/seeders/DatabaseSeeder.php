<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Comment;
use App\Models\Genre;
use App\Models\ListeningHistory;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'CloudMusic Admin', 'username' => 'admin', 'email' => 'admin@cloudmusic.test',
            'password' => Hash::make('CloudMusic@123'), 'role' => 'admin', 'is_active' => true,
            'bio' => 'Quản trị viên hệ thống CloudMusic.',
        ]);
        $artist = User::create([
            'name' => 'Luna Waves', 'username' => 'lunawaves', 'email' => 'artist@cloudmusic.test',
            'password' => Hash::make('CloudMusic@123'), 'role' => 'artist', 'is_active' => true,
            'bio' => 'Nghệ sĩ độc lập chuyên ambient, lo-fi và electronic.',
        ]);
        $artist2 = User::create([
            'name' => 'Neon District', 'username' => 'neondistrict', 'email' => 'neon@cloudmusic.test',
            'password' => Hash::make('CloudMusic@123'), 'role' => 'artist', 'is_active' => true,
            'bio' => 'Electronic duo from the city after midnight.',
        ]);
        $listener = User::create([
            'name' => 'Minh Anh', 'username' => 'minhanh', 'email' => 'listener@cloudmusic.test',
            'password' => Hash::make('CloudMusic@123'), 'role' => 'listener', 'is_active' => true,
        ]);

        $genreNames = ['Pop', 'Hip-hop', 'R&B', 'Electronic', 'Lo-fi', 'Rock', 'Indie', 'Ambient'];
        $genres = collect($genreNames)->mapWithKeys(function ($name) {
            $genre = Genre::create(['name' => $name, 'slug' => Str::slug($name), 'description' => "Tuyển tập {$name} trên CloudMusic.", 'is_active' => true]);
            return [$name => $genre];
        });

        $album = Album::create([
            'user_id' => $artist->id, 'title' => 'Signals at Dawn', 'slug' => 'signals-at-dawn',
            'description' => 'Ba bản phác thảo âm thanh được tạo riêng cho dữ liệu demo.',
            'cover_path' => 'demo-covers/dawn.svg', 'release_date' => now()->subDays(14), 'visibility' => 'public',
        ]);

        $songData = [
            ['Lunar Dawn', 'music/demo/lunar-dawn.wav', 'demo-covers/dawn.svg', 'Ambient', 12, 860, $artist, $album, 1],
            ['Orange Skyline', 'music/demo/orange-skyline.wav', 'demo-covers/orange.svg', 'Lo-fi', 14, 640, $artist, $album, 2],
            ['Quiet Satellites', 'music/demo/quiet-satellites.wav', 'demo-covers/satellite.svg', 'Electronic', 13, 510, $artist, $album, 3],
            ['Neon After Rain', 'music/demo/neon-after-rain.wav', 'demo-covers/neon.svg', 'Electronic', 15, 920, $artist2, null, 1],
        ];
        $songs = collect();
        foreach ($songData as [$title, $path, $cover, $genre, $duration, $plays, $owner, $songAlbum, $track]) {
            $songs->push(Song::create([
                'user_id' => $owner->id, 'genre_id' => $genres[$genre]->id, 'album_id' => $songAlbum?->id,
                'title' => $title, 'slug' => Str::slug($title),
                'description' => 'Bản âm thanh demo nguyên bản được tạo bằng sóng tổng hợp, không sử dụng nội dung có bản quyền.',
                'audio_path' => $path, 'audio_mime' => 'audio/wav', 'cover_path' => $cover,
                'duration_seconds' => $duration, 'track_number' => $track, 'release_date' => now()->subDays(10),
                'visibility' => 'public', 'status' => 'published', 'play_count' => $plays,
            ]));
        }

        $playlist = Playlist::create([
            'user_id' => $listener->id, 'name' => 'Late Night Focus', 'slug' => 'late-night-focus',
            'description' => 'Những âm thanh nhẹ để tập trung vào ban đêm.', 'visibility' => 'public',
        ]);
        foreach ($songs as $index => $song) {
            $playlist->songs()->attach($song->id, ['position' => $index + 1]);
        }
        $listener->likedSongs()->attach($songs->take(2)->pluck('id'));
        Song::whereIn('id', $songs->take(2)->pluck('id'))->increment('like_count');
        $listener->following()->attach([$artist->id, $artist2->id]);

        Comment::create(['user_id' => $listener->id, 'song_id' => $songs[0]->id, 'content' => 'Phần không gian âm thanh rất dễ chịu.']);
        $songs[0]->increment('comment_count');
        ListeningHistory::create(['user_id' => $listener->id, 'song_id' => $songs[0]->id, 'last_position_seconds' => 5, 'listened_at' => now()->subMinutes(20)]);
    }
}
