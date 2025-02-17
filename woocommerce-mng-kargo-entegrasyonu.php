<?php
/**
 * Plugin Name: WooCommerce MNG Kargo Entegrasyonu
 * Description: WooCommerce ile MNG Kargo API entegrasyonu.
 * Version: 1.0
 * Author: Senin Adın
 */

defined('ABSPATH') || exit;

// ✅ Admin Ayarlarını Yükle
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';

// ✅ API İşlemlerini Yükle
require_once plugin_dir_path(__FILE__) . 'includes/api-handler.php';

// ✅ Sipariş Yönetimini Yükle
require_once plugin_dir_path(__FILE__) . 'includes/order-handler.php';