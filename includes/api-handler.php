<?php
// ✅ Token Alma
function mng_get_api_token() {
    $client_id = get_option('mng_client_id');
    $client_secret = get_option('mng_client_secret');

    // ✅ Kimlik bilgilerini kontrol et
    if (empty($client_id) || empty($client_secret)) {
        error_log('[MNG] Hata: Client ID veya Secret boş!');
        return false;
    }

    $response = wp_remote_post('https://apizone.mngkargo.com.tr/token', [
        'body' => [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'grant_type'    => 'client_credentials'
        ],
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ],
        'timeout' => 20
    ]);

    // ✅ HTTP isteğindeki hataları logla
    if (is_wp_error($response)) {
        error_log('[MNG] Token İsteği Hatası: ' . $response->get_error_message());
        return false;
    }

    // ✅ HTTP durum kodunu kontrol et
    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code != 200) {
        error_log("[MNG] Token Alınamadı! HTTP Kodu: $status_code");
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['access_token'])) {
        error_log('[MNG] Token Yanıtı Geçersiz: ' . print_r($body, true));
        return false;
    }

    update_option('mng_api_token', $body['access_token']);
    update_option('mng_token_expiry', time() + $body['expires_in']);
    return $body['access_token'];
}

// ✅ Sipariş Oluşturma
function mng_create_shipment($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return false;

    $token = get_option('mng_api_token');
    if (get_option('mng_token_expiry') < time()) {
        $token = mng_get_api_token(); // Token'ı yenile
    }

    $shipping = $order->get_address('shipping');
    $items = $order->get_items();
    $total_weight = 0;

    foreach ($items as $item) {
        $product = $item->get_product();
        $total_weight += $product->get_weight() * $item->get_quantity();
    }

    $request_body = [
        'orderNumber' => (string) $order_id,
        'receiverName' => $shipping['first_name'] . ' ' . $shipping['last_name'],
        'receiverPhone' => $order->get_billing_phone(),
        'receiverAddress' => $shipping['address_1'],
        'receiverCity' => $shipping['city'],
        'receiverDistrict' => $shipping['state'],
        'receiverPostalCode' => $shipping['postcode'],
        'weight' => max(1, round($total_weight)), // Min 1 kg
        'desi' => max(1, round($total_weight * 0.5)) // Örnek desi hesaplama
    ];

    $response = wp_remote_post('https://apizone.mngkargo.com.tr/orders', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode($request_body),
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        error_log('[MNG] Sipariş Hatası: ' . $response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code == 201 && isset($body['trackingNumber'])) {
        $order->update_meta_data('_mng_tracking', $body['trackingNumber']);
        $order->save();
        return true;
    } else {
        error_log('[MNG] Hata Kodu: ' . $status_code . ' | Yanıt: ' . print_r($body, true));
        return false;
    }
}