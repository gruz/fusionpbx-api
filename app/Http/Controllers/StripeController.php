<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Payment;

class StripeController extends Controller
{
    public function setupIntent($sum)
    {
        /**
         * @var \App\Models\User
         */
        $user = auth()->user();
        $user->createOrGetStripeCustomer();
        // $intent = $user->createSetupIntent();

        if ($sum < config('payment.stripe.min')) {
            return response()->json([
                'status' => false,
                'message' => __('Minimum amount is :amount', [ 'amount' => config('payment.stripe.min')] ),
            ]);
        }

        $options = [
            'amount' => $sum * 100,
            'currency' => 'usd',
            // 'setup_future_usage' => 'off_session',
            'customer' => $user->stripeId(),
            'metadata' => [
                'domain_name' => $user->domain_name,
            ],
        ];

        $intent = $user->stripe()->paymentIntents->create($options);

        return response()->json([
            'status' => true,
            'intent' => $intent->client_secret
        ]);
    }

    /**
     * handling payment with POST
     */
    public function payAmount(Request $request)
    {
        /**
         * @var User
         */
        $user          = $request->user();
        $paymentMethod = $request->input('payment_method');

        try {
            $payment = new Payment(Cashier::stripe()->paymentIntents->retrieve(
                $paymentMethod, ['expand' => ['payment_method']])
            );

            $sum = $payment->rawAmount();
            $sum = $sum / 100;

            if (!$payment->isSucceeded()) {
                $msg = __('Could not process payment');
                \Log::error($msg);

                throw new \Exception($msg);
            }

        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }

        sleep(1);

        return back()->with('message', __('Balance updated successfully!'));
    }
}
