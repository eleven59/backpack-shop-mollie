<?php

return [
    'currency' => 'EUR', // See https://docs.mollie.com/payments/multicurrency
    'webhook_url' => '/mollie/webhook', // Never seen by users, only used by Mollie to send payment updates
    'locale' => 'nl_NL', // See https://docs.mollie.com/reference/v2/methods-api/list-methods under "locale"
];
