<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\OAuth;
use Stripe\Webhook;

class StripeController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function getConnectUrl()
    {
        $params = http_build_query([
            'response_type' => 'code',
            'scope'         => 'read_write',
            'redirect_uri'  => env('APP_URL') . '/api/admin/oauth/callback',
        ]);
        return response()->json([
            'url' => "https://connect.stripe.com/oauth/authorize?$params"
        ]);
    }

    public function handleConnectCallback(Request $request)
    {
        $resp = OAuth::token([
            'grant_type' => 'authorization_code',
            'code'       => $request->get('code'),
        ]);
        $acctId = $resp->stripe_user_id;
        // Sla $acctId op bij event of user
        // e.g. auth()->user()->update(['stripe_account_id'=>$acctId]);
        return response()->json(['connected' => true]);
    }

    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event     = Webhook::constructEvent(
            $payload,
            $sigHeader,
            env('STRIPE_WEBHOOK_SECRET')
        );

        if ($event->type === 'payment_intent.succeeded') {
            $pi      = $event->data->object;
            $contrib = \App\Models\Contribution::where(
                'stripe_payment_intent_id',
                $pi->id
            )->first();
            if ($contrib) {
                $contrib->update([
                    'status'            => 'paid',
                    'stripe_charge_id'  => $pi->charges->data[0]->id,
                ]);
            }
        }

        return response()->json(['received' => true]);
    }
}
