<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateSongRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('song')) ?? false; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'genre_id' => ['nullable', 'exists:genres,id'],
            'album_id' => ['nullable', Rule::exists('albums', 'id')->where(fn ($q) => $q->where('user_id', $this->user()->id))],
            'audio' => ['nullable', File::types(config('music.allowed_audio_types'))->max(config('music.max_audio_mb') * 1024)],
            'cover' => ['nullable', File::image()->max(config('music.max_image_mb') * 1024), 'mimes:jpg,jpeg,png,webp'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:7200'],
            'track_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'release_date' => ['nullable', 'date'],
            'visibility' => ['required', Rule::in(['public', 'private'])],
        ];
    }
}
