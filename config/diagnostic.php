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

        // Patient website account (login = phone number, first password = phone number)
        'portal_force_password_change' => 1,
    ],

    /*
     | Super Admin: everyone with this role can do everything — every permission (including ones added later),
     | every branch and any discount — without ticking anything on the role's permission page.
     | Set SUPER_ADMIN_FULL_ACCESS=false in .env to make Super Admin use ticked permissions like other roles.
     */
    'super_admin' => [
        'role' => env('SUPER_ADMIN_ROLE', 'Super Admin'),
        'full_access' => (bool) env('SUPER_ADMIN_FULL_ACCESS', true),
    ],

    // Public website text edited in Admin → Website → Website content. Empty = the built-in wording.
    'website' => [
        'hero_eyebrow_bn' => '', 'hero_eyebrow_en' => '',
        'hero_lead_bn' => '', 'hero_lead_en' => '',
        'hero_image' => '',
        'hero_popular' => 'CBC, HbA1c, Lipid Profile, TSH, USG',
        'stat_years' => 0,          // 0 = hidden
        'stat_patients' => 0,       // 0 = hidden
        'home_collection_enabled' => 1,
        'home_collection_areas_bn' => '', 'home_collection_areas_en' => '',
        'google_reviews_url' => '',
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
