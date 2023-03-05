<?php

namespace App\Actions\User;

use Illuminate\Support\Facades\Storage;
use Throwable;

class DeleteAvatar {
    public function execute(string $imagePath, string $folderName): void
    {
        $pos = strpos($imagePath, $folderName);
        if ($pos === false) return;

        $relativePath = substr($imagePath, $pos);

        try {
            Storage::delete($relativePath);
        } catch(Throwable $th) {
            report($th);
        }
    }
}
