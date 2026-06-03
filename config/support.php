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
    // Keys are the displayed labels; values are the internal type stored on the ticket.
    'subjects' => [
        'Billing or payment issue'        => 'billing',
        'Order or delivery problem'       => 'order',
        'Voucher or game key not working' => 'technical',
        'Account or login help'           => 'account',
        'Gacha or rewards question'       => 'gacha',
        'General question'                => 'general',
    ],
];
