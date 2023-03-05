<?php

namespace App\Actions\User;

use App\Models\User;

class SendEmailVerification
{
    public function handle(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }
}
