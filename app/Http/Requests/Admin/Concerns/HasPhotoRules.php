<?php

namespace App\Http\Requests\Admin\Concerns;

trait HasPhotoRules
{
    /**
     * Only real JPEG/PNG uploads: checked by extension, by sniffed MIME type, and again
     * when StaffPhotoProcessor decodes it. 2 MB cap matches PHP's default upload limit.
     *
     * @return array<int, string>
     */
    public static function photoRules(): array
    {
        return [
            'nullable', 'file',
            'mimes:jpg,jpeg,png',
            'mimetypes:image/jpeg,image/png',
            'max:2048',
            'dimensions:min_width=100,min_height=100,max_width=6000,max_height=6000',
        ];
    }

    public function messages(): array
    {
        return [
            'photo.mimes' => 'The photo must be a JPEG or PNG image.',
            'photo.mimetypes' => 'The photo must be a JPEG or PNG image.',
            'photo.max' => 'The photo must not be larger than 2 MB.',
            'photo.dimensions' => 'The photo must be at least 100×100 pixels and no larger than 6000×6000.',
            'photo.uploaded' => 'The photo failed to upload. It may be larger than the server allows (2 MB).',
        ];
    }
}
