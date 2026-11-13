<?php

/*
| Predefined website colour themes. The admin picks one in Settings → Website theme.
| Every preset was checked for WCAG AA contrast (4.5:1) for: white text on primary/hover,
| primary text on white and on the alt background, and secondary_ink text on white and on its tint.
| Only add presets that pass the same checks.
*/

return [
    'default' => 'ocean',

    'presets' => [
        'ocean' => [
            'label' => 'Ocean Blue',
            'description' => 'Trustworthy blue with a teal accent (brand guideline default).',
            'primary' => '#1557A6', 'primary_hover' => '#104780', 'primary_deep' => '#0F3F7A',
            'secondary' => '#159B83', 'secondary_ink' => '#0B6B5A',
            'heading' => '#17324D', 'alt' => '#F4F8FC',
        ],
        'emerald' => [
            'label' => 'Emerald Care',
            'description' => 'Calm medical green with a blue accent.',
            'primary' => '#0E7A6D', 'primary_hover' => '#0B6359', 'primary_deep' => '#084C45',
            'secondary' => '#3B82F6', 'secondary_ink' => '#1D4ED8',
            'heading' => '#123532', 'alt' => '#F1F8F6',
        ],
        'indigo' => [
            'label' => 'Royal Indigo',
            'description' => 'Modern, premium indigo with a sky accent.',
            'primary' => '#4338CA', 'primary_hover' => '#3730A3', 'primary_deep' => '#2E2A85',
            'secondary' => '#0EA5E9', 'secondary_ink' => '#0369A1',
            'heading' => '#1E1B4B', 'alt' => '#F5F5FD',
        ],
        'crimson' => [
            'label' => 'Heart Crimson',
            'description' => 'Warm red for cardiac or emergency focused centres.',
            'primary' => '#B91C3C', 'primary_hover' => '#9B1632', 'primary_deep' => '#7F1229',
            'secondary' => '#0D9488', 'secondary_ink' => '#0F766E',
            'heading' => '#2B1A20', 'alt' => '#FDF5F6',
        ],
        'violet' => [
            'label' => 'Violet Bloom',
            'description' => 'Soft violet with a pink accent, good for women & child care.',
            'primary' => '#6D28D9', 'primary_hover' => '#5B21B6', 'primary_deep' => '#4C1D95',
            'secondary' => '#EC4899', 'secondary_ink' => '#BE185D',
            'heading' => '#2A1554', 'alt' => '#F8F5FE',
        ],
        'sky' => [
            'label' => 'Clear Sky',
            'description' => 'Bright sky blue with a fresh green accent.',
            'primary' => '#0369A1', 'primary_hover' => '#075985', 'primary_deep' => '#0C4A6E',
            'secondary' => '#16A34A', 'secondary_ink' => '#166534',
            'heading' => '#0F2A3D', 'alt' => '#F0F7FB',
        ],
    ],
];
