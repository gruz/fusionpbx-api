@props(['intent'])

@if (session('message'))
    <div class="alert alert-success" role="alert">{{ session('message') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif

<x-form method="POST" :action="route('pay.amount')" class="card-form mt-3 mb-3 bg-gray-100 p-5 rounded">
    <x-form-input type="hidden" name="payment_method" class="payment-method" />

    <div class="flex items-center">
        <div class="px-4">
            US Dollar <span class="red-700">*</span>
        </div>
        <div>
            <input class="block w-full mt-1 rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50  payment-amout rounded" required="required" placeholder="10" name="amount" value="" type="number">
        </div>
    </div>
    {{-- <x-form-input type="number" name="amount" class="payment-amout rounded" required label="$" placeholder /> --}}
    {{-- <div class="flex">
        <div class="flex-1">
        </div>
        <div class="flex-3">
        </div>
    </div> --}}
    <x-form-input class="StripeElement mb-3" name="card_holder_name" placeholder="{{ __('Card holder name') }}"  />
    <div class="col-lg-4 col-md-6">
        <div id="card-element"></div>
    </div>
    <div id="card-errors" role="alert"></div>
    <div class="form-group mt-3">
        <x-form-submit class="btn btn-primary pay" >
            Purchase
        </x-form-submit>
    </div>
</x-form>

@push('styles')
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" /> --}}
    <style>
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }

        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }

        .StripeElement--invalid {
            border-color: #fa755a;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }

    </style>
@endpush
@push('scripts-bottom')
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> --}}

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let stripe = Stripe("{{ env('STRIPE_KEY') }}")
        let elements = stripe.elements()
        let style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            },
        }
        let card = elements.create('card', {
            style: style,
            hidePostalCode: true
        })
        card.mount('#card-element')
        let paymentMethod = null

        let cardForm = document.querySelector('.card-form');

        cardForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            let buttonPay = document.querySelector('button.pay');
            buttonPay.disabled = true;
            if (paymentMethod) {
                return true
            }

            let cardHolderName = document.querySelector('[name=card_holder_name]').value;
            if (cardHolderName === '') {
                cardHolderName = null;
            }

            let result = await stripe.confirmCardSetup(
                "{{ $intent->client_secret }}", {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: cardHolderName
                        }
                    }
                }
            );

            if (result.error) {
                document.querySelector('#card-errors').innerHTML = result.error.message;
                buttonPay.disabled = false;
            } else {
                paymentMethod = result.setupIntent.payment_method
                document.querySelector('.payment-method').value = paymentMethod;
                document.querySelector('.card-form').submit()
            }

            return false
        });
    </script>
@endpush