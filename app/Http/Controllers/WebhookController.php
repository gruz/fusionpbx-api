<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Events\PaymentReceivedEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CGRTAddCreditBalanceFailedNotification;

class WebhookController extends CashierController
{
    /**
     * handling payment with POST
     */
    public function handleChargesucceeded($payload)
    {
        // \Log::info($payload);
        $mainAdminEmail = config('mail.error_email');
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        try {
            $sum = $payload['data']['object']['amount'];
            $sum = $sum / 100;

            if (!$user) {
                $msg = 'Payment charged, but could not find payment owner .
                Please contact administrator. The stripe payment id is ' . $payload['data']['object']['id'];

                $options = [
                    'payment_processor' => 'stripe',
                    'sum' => $sum,
                    'currency' => $payload['data']['object']['currency'],
                    'payment_id' => $payload['data']['object']['id'],
                    'message' => $msg,
                ];

                Notification::route('mail', $mainAdminEmail)
                    ->notify(new CGRTAddCreditBalanceFailedNotification($user, $options));

                \Log::error($msg);

                throw new \Exception($msg);
            }


            // $stripeCharge = $user->charge($sum * 100, $paymentMethod, $options);
            $user_balance = $user->addCGRTBalance($sum, 'Stripe ID : ' . $payload['data']['object']['id']);

            $options = [
                'payment_processor' => 'stripe',
                'sum' => $sum,
                'currency' => $payload['data']['object']['currency'],
                'payment_id' => $payload['data']['object']['id'],
                'user_balance' => $user_balance,
            ];

            if ($user_balance === 0 || $user_balance < $sum) {

                $msg = 'Payment charged, but could not update internal balance.
                    Please contact administrator. The stripe payment id is ' . $payload['data']['object']['id'];
                \Log::error($msg);

                $options['message'] = $msg;

                Notification::route('mail', $mainAdminEmail)
                    ->notify(new CGRTAddCreditBalanceFailedNotification($user, $options));


                throw new \Exception($msg);
            }
        // } catch (IncompletePayment $exception) {
        //     return redirect()->route(
        //         'cashier.payment',
        //         [$exception->payment->id, 'redirect' => route('dashboard')]
        //     );
        } catch (\Exception $exception) {
            return $exception->getMessage();
            // return back()->with('error', $exception->getMessage());
        }

        event(new PaymentReceivedEvent($user, $options));

        // dd('aaa');

        // d($stripeCharge, $stripeCharge->__get('id'));
        // exit;

        // // Here, complete the order, like, send a notification email
        // $user->notify(new OrderProcessed($product));

        // return back()->with('message', __('Balance updated successfully!'));
        return __('Balance updated successfully!');
    }
}
