<?php

namespace App\Http\Controllers;

use App\Models\{Event, Contribution};
use Illuminate\Http\Request;
use Stripe\StripeClient;

class ContributionController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function storeGuest(Request $request, string $join_code)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.50',
        ]);

        $event = Event::where('join_code', $join_code)->firstOrFail();
        // abort_unless(
        //     $event->stripe_account_id,
        //     422,
        //     'Organisator heeft nog geen Stripe-account gekoppeld'
        // );

        $contrib = Contribution::create([
            'event_id'   => $event->id,
            'amount'     => $data['amount'],
            'status'     => 'pending',
        ]);

        $pi = $this->stripe->paymentIntents->create([
            'amount'               => intval($data['amount'] * 100),
            'currency'             => 'eur',
            'payment_method_types' => ['card'],
            'metadata'             => ['contrib_id' => $contrib->id],
            // 'transfer_data'        => [
            //     'destination' => $event->stripe_account_id,
            //     'amount'      => intval($data['amount'] * 100 * (1 - env('PLATFORM_FEE_PERCENT') / 100)),
            // ],
        ]);

        $contrib->update([
            'stripe_payment_intent_id' => $pi->id,
        ]);

        return response()->json([
            'client_secret' => $pi->client_secret
        ], 201);
    }
}
