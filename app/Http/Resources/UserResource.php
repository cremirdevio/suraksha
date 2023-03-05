<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profileAvatar = $this->profile_image;
        if(is_null($profileAvatar)) {
            $emailHash = md5(strtolower(trim($this->email)));
        } else {
            $profileAvatar = Str::contains($profileAvatar, 'http') ? $profileAvatar : Storage::url($profileAvatar);
        }

        return [
            "id" => $this->id,
            "firstname" => $this->firstname,
            "lastname" => $this->lastname,
            "email" => $this->email,
            "image_url" => $profileAvatar ? $profileAvatar : "https://www.gravatar.com/avatar/{$emailHash}?d=identicon",
            "created_at" => $this->created_at,
        ];
    }
}
