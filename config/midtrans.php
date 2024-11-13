<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-ktN_hAi4_tV12DW0AW5PYLkR'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-1JTCrR9hP3kq-wie'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'snap_url' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),
];