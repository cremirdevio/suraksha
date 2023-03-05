<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Requests\VerifyEmailRequest;

/**
 * @group Authentication
 *
 * Cardvest API uses Auth Token to authenticate requests. You can get the users Auth Token on login.
 */
class AuthController extends Controller
{
    /**
     * User's Registration
     *
     * Create an account for a new Cardvest customer.
     *
     * @unauthenticated
     * @response scenario=success {}
     */
    public function create(Request $request, CreateNewUser $newUser)
    {

        DB::beginTransaction();

        try {
            $user = $newUser->create($request);
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return response()->json([
            'message' => 'User created successfully.',
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * User's Login
     *
     * Authenticate the user to to enable them perform trades and other actions.
     *
     * @unauthenticated
     * @response scenario=success { "message": "Login successful. Copy the authentication token for use in the data body", "data": { "token": "4|bczkFaZxvTZjtAf8ov3jYZ7dhXsFYJCr61QEX43s" } }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
        ]);

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            return response()->json([
                'message' => "You don't have an account with us",
                'data' => null
            ], 400);
        }

        $user->tokens()->delete();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // $request->userAgent()
        $agent = $request->device_name ?? $request->userAgent();

        return response()->json([
            'message' => "Login successful",
            'data' => [
                "token" => $user->createToken($user->username)->plainTextToken,
                "user" => new UserResource($user)
            ]
        ]);
    }

    /**
     * User's Logout
     *
     * @unauthenticated
     */
    public function logout(Request $request)
    {
        $user = User::findOrFail(auth()->id());
        $user->tokens()->delete();
        return response()->json(['message' => 'User logged out.'], 200);
    }

    /**
     * Fetch Authenticated User's Data
     *
     * This endpoint is used to get the user's profile data.
     *
     * @response scenario=success {"data":{"id":1,"username":"jerad67","phonenumber":"08167297386","email":"josephajibodu@gmail.com","email_verified":false,"image_url":"https:\/\/www.gravatar.com\/avatar\/cc5ee67195155ff5523a0248355443d6?d=identicon","created_at":"2022-09-20T16:39:25.000000Z"}}
     */
    public function user()
    {
        return response()->json([
            'message' => 'Successful',
            'data' => new UserResource(auth()->user()),
        ]);
    }

    /**
     * Forgot Password
     *
     * This endpoint sends a password reset email to the email address specified.
     *
     * @unauthenticated
     * @response scenario=success { "message": "We have emailed your password reset link!" }
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        return (new PasswordResetLinkController)->store($request);
    }

    /**
     * Reset Password
     *
     * This endpoint expects a string <b>email</b> field, a <b>password</b> field,
     * a <b>password_confirmation</b> field, and a hidden field named <b>token</b> that contains the token parameter that was part of the reset link.
     *
     * @unauthenticated
     * @response scenario=success {"message":"Your password has been reset!"}
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        return App::make(NewPasswordController::class)->store($request);
    }

    /**
     * Verify Email
     *
     * Mark the user's email address as verified.
     *
     * @param  VerifyEmailRequest  $request
     */
    public function verifyEmail(VerifyEmailRequest $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json([], 204);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([], 204);
    }

}
