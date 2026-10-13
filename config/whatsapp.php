<?php

/*
|--------------------------------------------------------------------------
| VexaHost WA Gateway
|--------------------------------------------------------------------------
|
| URL & API key utama dikelola lewat menu Admin > Pengaturan Perusahaan
| (tabel company_settings). Nilai .env di bawah hanya fallback.
|
*/

return [
    'api_url' => env('WA_API_URL', 'https://wa.vexahostcloud.my.id/api/v1/messages/text'),
    'api_key' => env('WA_API_KEY', ''),
    'webhook_secret' => env('WA_WEBHOOK_SECRET', ''),
    'admin_phones' => array_values(array_filter(array_map('trim', explode(',', env('WA_ADMIN_PHONES', '085808749131'))))),
    'admin_notification_email' => env('ADMIN_NOTIFICATION_EMAIL', env('MAIL_ADMIN_ADDRESSES', 'support@vexahostcloud.my.id,vexahostcloudtech@gmail.com')),

    // Harga paket default (IDR)
    'packages' => [
        'landing_page' => [
            'name' => 'Landing Page',
            'price' => 499000,
            'description' => 'Website 1 halaman konversi tinggi, cepat, mobile-friendly.',
        ],
        'company_profile' => [
            'name' => 'Company Profile',
            'price' => 999000,
            'description' => 'Website profesional profil perusahaan lengkap dengan halaman tentang, layanan, & kontak.',
        ],
        'toko_kasir' => [
            'name' => 'Toko & Kasir POS',
            'price' => 1500000,
            'description' => 'Website e-commerce / katalog produk terintegrasi sistem kasir POS UMKM.',
        ],
        'custom' => [
            'name' => 'Custom Web App',
            'price' => 2500000,
            'description' => 'Aplikasi web atau sistem informasi kustom sesuai kebutuhan bisnis.',
        ],
    ],

    // Harga maintenance bulanan default (IDR)
    'default_maintenance_price' => 150000,
];
