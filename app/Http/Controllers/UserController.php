<?php

namespace App\Http\Controllers;

use App\Actions\User\DeleteAvatar;
use App\Actions\User\EmailVerification;
use App\Actions\User\SendEmailVerification;
use App\Actions\User\UploadAvatar;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;
use Illuminate\Http\JsonResponse;

/**
 * @group User
 *
 * This resource returns the profile details of the currently authenticated user. The users wallet detail inclusive.
 */
class UserController extends Controller
{
    /**
     * Fetch the authenticated users detail
     *
     * @response scenario=success {"data":{"id":1,"username":"jerad67","phonenumber":"08167297386","email":"josephajibodu@gmail.com","email_verified":false,"image_url":"https:\/\/www.gravatar.com\/avatar\/cc5ee67195155ff5523a0248355443d6?d=identicon","created_at":"2022-09-20T16:39:25.000000Z"}}
     */
    public function me() : JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => "Profile details fetched",
            "data" => new UserResource(auth()->user())
        ]);
    }

    /**
     * Update the user's profile details
     *
     * @response scenario=success {"data":{"id":1,"username":"jerad67","phonenumber":"08167297386","email":"josephajibodu@gmail.com","email_verified":false,"image_url":"https:\/\/www.gravatar.com\/avatar\/cc5ee67195155ff5523a0248355443d6?d=identicon","created_at":"2022-09-20T16:39:25.000000Z"}}
     */
    public function update(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'firstname' => ['required', 'alpha_dash', 'max:255'],
            'lastname' => ['required', 'alpha_dash', 'max:255'],
        ]);

        $user = User::findOrFail(auth()->id());
        $user->update($validated);

        return response()->json([
            "success" => true,
            "message" => "Profile details updated",
            "data" => new UserResource($user)
        ]);
    }

    /**
     * Update the users authentication password.
     *
     * @response scenario=success {}
     */
    public function updatePassword(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', new Password, 'confirmed'],
            'password_confirmation' => ['required', 'string', new Password],
        ]);

        $user = User::findOrFail(auth()->id());

        if (!isset($request->current_password) || ! Hash::check($request->current_password, $user->password)) {
            abort(400, __('The provided password does not match your current password.'));
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return response()->json([
            "success" => true,
            "message" => "User's password updated",
            "data" => new UserResource($user)
        ]);
    }

    /**
     * Resend email verification to users
     *
     * @response scenario=success {"status":true,"message":"Verification email is being processed"}
     */
    public function sendVerification(Request $request, SendEmailVerification $verification) : JsonResponse
    {
        try {
            $verification->handle(auth()->user());
        } catch (\Throwable $th) {
            if (app()->isProduction()) {
                report($th);
                return response()->json([
                    "success" => false,
                    "message" => "Verification email not sent",
                    "data" => []
                ], 500);
            } else {
                throw $th;
            }
        }

        return response()->json([
            "success" => true,
            "message" => "Verification email is being processed",
            "data" => []
        ], 202);
    }

    /**
     * Change the user's profile image.
     *
     * @response scenario=success {"message":"Profile image updated","data":"http:\/\/cardvest.test\/storage\/profile-avatars\/ve1676071780.jpg"}
     */
    public function uploadAvatar(Request $request, UploadAvatar $uploadAvatar, DeleteAvatar $deleteAvatar): JsonResponse
    {
        $request->validate([
            'image' => 'required|mimes:png,jpg,gif|max:5120'
        ]);
        $fileToUpload = $request->image;
        $user = User::findOrFail(auth()->id());

        try {
            // Get the previous profile image
            $previousFilePath = $user->profile_image;
            $filepath = $uploadAvatar->execute($fileToUpload, 'profile-avatars');
            $user->profile_image = $filepath;
            $user->save();

            if (!is_null($previousFilePath)) {
                $deleteAvatar->execute($previousFilePath, 'profile-avatars');
            }
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage(),
                "error" => null
            ], $th->getCode());
        }

        return response()->json([
            "success" => true,
            "message" => "Profile image updated",
            "data" => $filepath
        ]);
    }

    /**
     * Delete the user's account
     *
     * @response scenario=success {}
     */
    public function destroyAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'current_password'],
        ], [
            'password.current_password' => 'Password is incorrect!'
        ]);

        $user = User::findOrFail(auth()->id());
        $user->tokens()->delete();

        return response()->json([
            "success" => true,
            "message" => "Account deleted",
            "data" => []
        ], 204);
    }
}
