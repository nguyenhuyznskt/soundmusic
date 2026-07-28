<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreSongRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isArtist() ?? false; }

    public function rules(): array
    {
        $audioKb = config('music.max_audio_mb') * 1024;
        $imageKb = config('music.max_image_mb') * 1024;

        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'genre_id' => ['nullable', 'exists:genres,id'],
            'album_id' => ['nullable', Rule::exists('albums', 'id')->where(fn ($q) => $q->where('user_id', $this->user()->id))],
            'audio' => ['required', File::types(config('music.allowed_audio_types'))->max($audioKb)],
            'cover' => ['nullable', File::image()->max($imageKb), 'mimes:jpg,jpeg,png,webp'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:7200'],
            'track_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'release_date' => ['nullable', 'date'],
            'visibility' => ['required', Rule::in(['public', 'private'])],
        ];
    }

    public function messages(): array
    {
        return [
            'duration_seconds.required' => 'Trình duyệt chưa đọc được thời lượng. Hãy chọn lại file âm thanh.',
            'audio.required' => 'Bạn chưa chọn file âm thanh.',
        ];
    }
}
