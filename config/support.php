<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Support inbox
    |--------------------------------------------------------------------------
    | Where new customer support tickets are emailed. The admin replies from
    | their own mail client; each notification sets Reply-To to the customer,
    | so a plain "Reply" reaches them directly.
    */
    'admin_address' => env('SUPPORT_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS', 'support@ridly.example')),

    // Predefined subjects offered in the customer form's combobox.
    'subjects' => [
        'Billing or payment issue',
        'Order or delivery problem',
        'Voucher or game key not working',
        'Account or login help',
        'Gacha or rewards question',
        'General question',
    ],
];
