<?php

namespace App\Http\Controllers;

use Stripe;
use Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Events\PaymentReceivedEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CGRTAddCreditBalanceFailedNotification;

class StripeController extends Controller
{
    /**
     * payment view
     */
    public function handleGet()
    {
        return view('stripe');
    }

    /**
     * handling payment with POST
     */
    public function handlePost(Request $request)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        Stripe\Charge::create([
            "amount" => 100 * 15,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "Making test payment."
        ]);

        Session::flash('success', __('Payment has been successfully processed.'));

        return back();
    }

    /**
     * payment view
     */
    public function show()
    {
        /**
         * @var User
         */
        $user = auth()->user();
        $intent = $user->createSetupIntent();
        return view('stripe-show', compact('intent'));
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
        $sum = $request->input('amount');
        $options = [
                'metadata' => [
                    'domain_name' => $user->domain_name,
                ],
            // 'params' => [
            //     'idempotency_key' => uniqid(),
            // ]
        ];

        try {
            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($paymentMethod);
            $stripeCharge = $user->charge($sum * 100, $paymentMethod, $options);
            $user_balance = $user->addCGRTBalance($sum, 'Stripe ID : ' . $stripeCharge->__get('id'));

            $options = [
                'payment_processor' => 'stripe',
                'sum' => $sum,
                'currency' => $stripeCharge->__get('currency'),
                'payment_id' => $stripeCharge->__get('id'),
                'user_balance' => $user_balance,
            ];

            if ($user_balance === 0 || $user_balance < $sum) {
                $mainAdminEmail = config('app.contact_email');

                Notification::route('mail', $mainAdminEmail)
                    ->notify(new CGRTAddCreditBalanceFailedNotification($user, $options));

                $msg = 'Payment charged, but could not update internal balance.
                    Please contacnt administrator. The stripe payment id is ' . $stripeCharge->__get('id');
                \Log::error($msg);

                throw new \Exception($msg);
            }
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }

        event(new PaymentReceivedEvent($user, $options));

        // dd('aaa');

        // d($stripeCharge, $stripeCharge->__get('id'));
        // exit;

        // // Here, complete the order, like, send a notification email
        // $user->notify(new OrderProcessed($product));

        return back()->with('message', __('Balance updated successfully!'));
    }
}
