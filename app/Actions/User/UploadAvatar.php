<?php

namespace App\Actions\User;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadAvatar {
    public function execute(UploadedFile $file, string $folderName): string
    {
        // Get the default filesystem
        $disk = config('filesystems.default');

        $fileExtention = $file->extension();
        // File extension issue (https://github.com/codebar-ag/laravel-flysystem-cloudinary#-file-extension-problem)
        // $filename = Str::slug(Str::random(2), '-') . time().'.'.$fileExtention;
        // Name without file extension

        if ($disk == 'cloudinary') {
            $filename = Str::slug(Str::random(2), '-') . time();
        } else {
            $filename = Str::slug(Str::random(2), '-') . time().'.'.$fileExtention;
        }

        $path = $file->storeAs(
            $folderName,
            $filename,
        );

        try {
            return Storage::url($path);
        } catch (\Throwable $th) {
            if (app()->isProduction()) {
                Log::info("Error Getting Storage URL");
                report($th);
            } else throw $th;
        }

        return "{$path}";
    }
}
