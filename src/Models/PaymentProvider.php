<?php

namespace Eleven59\BackpackShopMollie\Models;

use Eleven59\BackpackShop\Models\Order;
use Illuminate\Http\Request;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Laravel\Facades\Mollie;

class PaymentProvider extends \Eleven59\BackpackShop\PaymentProvider
{
    public const STATUS_TRANSLATIONS = [
        'open' => 'new',
        'canceled' => 'cancelled',
        'pending' => 'new',
        'authorized' => 'new',
        'expired' => 'cancelled',
        'failed' => 'cancelled',
        'paid' => 'paid',
        'unknown' => 'new',
    ];
    public const DEPENDENCIES = [];

    /**
     * @param array $options
     * Useful key: $options['amount'] = ['currency'=>'', 'value'=>'']
     * Both currency and value need to be present. If provided, the list of methods is filtered automatically
     * based on the available methods for this currency and amount.
     * @return array
     */
    public static function getPaymentMethods(array $options = []) :array
    {
        $options = array_merge($options, [
            "sequenceType" => "first",
            "locale" => config('eleven59.backpack-shop-mollie.locale', 'nl_NL'),
//            "include" => "issuers",
        ]);

        try {
            $result = Mollie::api()->methods()->allActive($options);
        } catch(ApiException $e) {
            return [];
        }

        $methods = [];
        foreach($result as $paymentMethod) {
            $methods[$paymentMethod->id] = [
                'id' => $paymentMethod->id,
                'description' => $paymentMethod->description,
                'dependencies' => [],
            ];
//            if (!empty($paymentMethod->issuers)) {
//                $methods[$paymentMethod->id]['dependencies']['issuers'] = [
//                    'id' => $paymentMethod->id . '-issuers',
//                    'name' => $paymentMethod->id . '_issuer',
//                    'values' => [],
//                ];
//                foreach($paymentMethod->issuers as $issuer) {
//                    $methods[$paymentMethod->id]['dependencies']['issuers']['values'][$issuer->id] = [
//                        'id' => $issuer->id,
//                        'description' => $issuer->name,
//                    ];
//                }
//            }
        }

        return $methods;
    }

    /**
     * Create a new Mollie Payment and return the checkout Url
     *
     * @param array $order_data
     * @return bool|array
     */
    public static function createPayment(array $order_data) :bool|array
    {
        $payment_description = config('eleven59.backpack-shop.payment-description') ??
            __('backpack-shop::order.payment-description', [':store_name' => env('APP_NAME')]);

        $webhook_url = $order_data['webhook_url'] ??
            env('APP_URL') . config('eleven59.backpack-shop-mollie.webhook_url', '/mollie/webhook');
        if(env('APP_DEBUG')) {
            $webhook_url = null;
        }

        $amount = number_format((float)$order_data['amount'], 2, '.', '');
        $payload = [
            'amount' => [
                'currency' => config('eleven59.backpack-shop-mollie.currency', 'EUR'),
                'value' => "{$amount}",
            ],
            'description' => $payment_description,
            'redirectUrl' => $order_data['return_url'],
            'webhookUrl' => $webhook_url,
            'method' => $order_data['payment_method'],
            'metadata' => [
                'order_id' => "{$order_data['order_id']}",
            ],
        ];

//        if ($order_data['payment_method'] === PaymentMethod::IDEAL)
//        {
//            $payload['issuer'] = $order_data['ideal_issuer'] ?? null;
//        }

//        try {
//        dd($payload);
            $payment = mollie()->payments()->create($payload);
            $checkout_url = $payment->getCheckoutUrl();
//        } catch (ApiException $e) {
//            return false;
//        }

        $result = [
            'id' => $payment->id,
            'mode' => $payment->mode,
            'status' => $payment->status ?? 'new',
            'checkout_url' => $checkout_url,
            'payload' => $payload,
        ];

        return $result;
    }

    /**
     * Retrieve the current payment status using the paymentId from Mollie
     *
     * @param $payment_id
     * @return bool|string
     */
    public static function getPaymentStatus($payment_id) :bool|string
    {
        try {
            $payment = Mollie::api()->payments()->get($payment_id);
        } catch (ApiException $e) {
            return false;
        }

        return $payment->status ?? 'unknown';
    }


    /**
     * Order fully processed?
     * (yes when status is paid)
     *
     * @param string $status
     * @return bool
     */
    public static function sendConfirmation(string $status) :bool
    {
        return $status === 'paid';
    }


    /**
     * Process webhook data and return payment Id
     * Mollie makes this easy; we can just pass it along
     *
     * @param Request $request
     * @return int|string
     */
    public static function getWebhookPaymentId(Request $request) :int|string
    {
        return $request->get('id', false);
    }


    /**
     * Process payment response data and return payment Id
     * Unfortunately, Mollie does not include it in the response, so we fetch it from the Order
     *
     * @param Request $request
     * @return int|string
     */
    public static function getResponsePaymentId(Request $request) :int|string
    {
        $order_id = $request->get('order');
        if(!$order = Order::find($order_id)) {
            return false;
        }
        return $order->payment_info->id ?? false;
    }
}
