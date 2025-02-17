<?php
// ✅ Sipariş Oluşturulunca Tetikle
add_action('woocommerce_order_status_processing', 'mng_auto_create_shipment');
function mng_auto_create_shipment($order_id) {
    if (get_option('mng_auto_shipment') == 'yes') {
        mng_create_shipment($order_id);
    }
}

// ✅ Admin Sipariş Detayına Takip Numarası Ekle
add_action('woocommerce_admin_order_data_after_shipping_address', 'mng_show_tracking_info');
function mng_show_tracking_info($order) {
    $tracking_number = $order->get_meta('_mng_tracking');
    if ($tracking_number) {
        echo '<p><strong>MNG Takip No:</strong> ' . esc_html($tracking_number) . '</p>';
    }
}

// ✅ Müşteriye Email ile Bildirim
add_filter('woocommerce_email_order_meta_fields', 'mng_add_email_tracking', 10, 3);
function mng_add_email_tracking($fields, $sent_to_admin, $order) {
    $tracking_number = $order->get_meta('_mng_tracking');
    if ($tracking_number && !$sent_to_admin) {
        $fields['mng_tracking'] = [
            'label' => 'Takip Numarası',
            'value' => $tracking_number
        ];
    }
    return $fields;
}