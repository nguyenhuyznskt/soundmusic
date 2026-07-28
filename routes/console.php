<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Artisan::command('cloudmusic:about', function () {
    $this->info('CloudMusic is ready. Run migrations and seeders before starting.');
})->purpose('Show CloudMusic project information');

Artisan::command('cloudmusic:doctor', function () {
    $failed = false;
    $extensions = ['pdo', 'mbstring', 'openssl', 'fileinfo'];
    if (config('database.default') === 'mysql') $extensions[] = 'pdo_mysql';

    $this->newLine();
    $this->info('CloudMusic environment check');
    foreach ($extensions as $extension) {
        $ok = extension_loaded($extension);
        $ok ? $this->line("  [OK] PHP extension: {$extension}") : $this->error("  [FAIL] Missing PHP extension: {$extension}");
        $failed = $failed || !$ok;
    }

    try {
        DB::connection()->getPdo();
        $this->line('  [OK] Database connection');
    } catch (Throwable $e) {
        $this->error('  [FAIL] Database: '.$e->getMessage());
        $failed = true;
    }

    foreach (['music/demo/lunar-dawn.wav', 'music/demo/orange-skyline.wav', 'music/demo/quiet-satellites.wav', 'music/demo/neon-after-rain.wav'] as $file) {
        $ok = Storage::disk('local')->exists($file);
        $ok ? $this->line("  [OK] Demo audio: {$file}") : $this->error("  [FAIL] Missing demo audio: {$file}");
        $failed = $failed || !$ok;
    }

    $writable = is_writable(storage_path()) && is_writable(storage_path('framework'));
    $writable ? $this->line('  [OK] Storage is writable') : $this->error('  [FAIL] Storage is not writable');
    $failed = $failed || !$writable;

    $this->newLine();
    if ($failed) {
        $this->error('Doctor found one or more problems. Fix them before starting the server.');
        return 1;
    }
    $this->info('All checks passed.');
    return 0;
})->purpose('Check PHP extensions, database, storage and demo audio files');
