<?php

/*
| Business-rule defaults. Values saved in Admin → Business Rules (settings table) override these.
*/

return [
    'defaults' => [
        'currency_symbol' => '৳',
        'invoice_prefix' => 'INV',
        'receipt_prefix' => 'MR',
        'vat_percent' => 0,
        'round_to_whole_taka' => 1,
        'default_delivery_hours' => 24,
        'home_collection_charge' => 0,

        // Referral commission
        'commission_enabled' => 1,
        'discount_bearer' => 'company',     // company | referrer | shared
        'commission_trigger' => 'fully_paid', // fully_paid | on_invoice

        // Reports
        'block_report_if_due' => 1,          // counter delivery
        'block_portal_report_if_due' => 1,   // patient portal download
    ],

    'discount_bearers' => [
        'company' => 'Company bears the discount (commission on full price)',
        'referrer' => 'Referrer bears the discount (deducted from commission)',
        'shared' => 'Shared 50 / 50',
    ],

    'commission_triggers' => [
        'fully_paid' => 'When the invoice is fully paid',
        'on_invoice' => 'As soon as the invoice is created',
    ],
];
