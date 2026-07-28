<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamController extends Controller
{
    public function __invoke(Request $request, Song $song): StreamedResponse
    {
        $allowed = $song->status === 'published' && $song->visibility === 'public'
            || $request->user() && ($request->user()->id === $song->user_id || $request->user()->isAdmin());
        abort_unless($allowed, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($song->audio_path), 404, 'Không tìm thấy file âm thanh.');

        $path = $disk->path($song->audio_path);
        $size = filesize($path);
        abort_if($size === false || $size < 1, 404);

        $start = 0;
        $end = $size - 1;
        $status = 200;
        $range = $request->header('Range');

        if ($range && preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
            $requestedStart = $matches[1] !== '' ? (int) $matches[1] : 0;
            $requestedEnd = $matches[2] !== '' ? (int) $matches[2] : $end;
            if ($requestedStart > $end || $requestedStart > $requestedEnd) {
                abort(416, 'Requested range not satisfiable.');
            }
            $start = max(0, $requestedStart);
            $end = min($end, $requestedEnd);
            $status = 206;
        }

        $length = $end - $start + 1;
        $headers = [
            'Content-Type' => $song->audio_mime ?: 'application/octet-stream',
            'Accept-Ranges' => 'bytes',
            'Content-Length' => (string) $length,
            'Cache-Control' => 'private, max-age=3600',
            'Content-Disposition' => 'inline; filename="'.addslashes(basename($path)).'"',
        ];
        if ($status === 206) $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";

        return response()->stream(function () use ($path, $start, $length) {
            $handle = fopen($path, 'rb');
            if ($handle === false) return;
            try {
                fseek($handle, $start);
                $remaining = $length;
                while ($remaining > 0 && !feof($handle)) {
                    $chunk = fread($handle, min(8192, $remaining));
                    if ($chunk === false || $chunk === '') break;
                    echo $chunk;
                    $remaining -= strlen($chunk);
                    if (connection_aborted()) break;
                }
            } finally {
                fclose($handle);
            }
        }, $status, $headers);
    }
}
