<?php

return [
    'max_audio_mb' => (int) env('MUSIC_MAX_AUDIO_MB', 50),
    'max_image_mb' => (int) env('MUSIC_MAX_IMAGE_MB', 5),
    'min_listen_seconds' => (int) env('MUSIC_MIN_LISTEN_SECONDS', 10),
    'allowed_audio_types' => ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'],
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp'],
];
