<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slugger
{
    /**
     * Create a URL-safe slug limited to a reasonable length.
     */
    public static function slug(string $value, int $maxLength = 80): string
    {
        $slug = Str::slug($value);
        $slug = trim($slug);

        if ($slug === '') {
            $slug = Str::random(10);
        }

        return Str::limit($slug, $maxLength, '');
    }
}
