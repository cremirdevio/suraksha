<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Newsletter\Facades\Newsletter;

/**
 * @group Customers Relation Management
 *
 * This API is used to manage the list of users registering on our platform
 */
class NewsletterController extends Controller
{
    /**
     * Subscribe User
     *
     * This endpoint is used to subscribe a user to our newsletter
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            "email" => ['required', 'email', 'max:255']
        ]);

        // Log the email address for later use
        Log::channel('newsletter')->info("📧 New Email Subscription; {$request->email}");

        // A call to mailchimp service
        Newsletter::subscribeOrUpdate($request->email);

        return response()->json([
            "success" => true,
            "message" => "You have been successfully subscribed to our newsletter.",
            "data" => []
        ]);
    }

    /**
     * Unsubscribe User
     *
     * This endpoint is used to unsubsribe a user from our newsletter
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            "email" => ['required', 'email', 'max:255']
        ]);

        // Log the email address for later use
        Log::channel('newsletter')->info("📧❌ Unsubscribe Me; {$request->email}");

        Newsletter::unsubscribe($request->email);

        return response()->json([
            "success" => true,
            "message" => "You have been successfully unsubscribed to our newsletter.",
            "data" => []
        ]);
    }
}
