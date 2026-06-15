<?php

if (!function_exists('get_app_domain')) {
    function get_app_domain() {
        return getenv('APP_DOMAIN') ?: 'm2profi.pro';
    }
}

if (!function_exists('get_app_url')) {
    function get_app_url() {
        return getenv('APP_URL') ?: 'https://m2profi.pro';
    }
}

if (!function_exists('get_public_domain')) {
    function get_public_domain() {
        return getenv('PUBLIC_DOMAIN') ?: 'm2profi.pro';
    }
}

if (!function_exists('get_public_url')) {
    function get_public_url() {
        return getenv('PUBLIC_URL') ?: 'https://m2profi.pro';
    }
}

if (!function_exists('get_support_email')) {
    function get_support_email() {
        return getenv('SUPPORT_EMAIL') ?: 'support@m2profi.pro';
    }
}

if (!function_exists('get_noreply_email')) {
    function get_noreply_email() {
        return getenv('NOREPLY_EMAIL') ?: 'noreply@m2profi.pro';
    }
}

if (!function_exists('build_feed_url')) {
    function build_feed_url($type, $homeId = null) {
        $baseUrl = get_app_url();
        switch ($type) {
            case 'domclick':
                return $baseUrl . "/sahmatka/domclick-{$homeId}.xml";
            case 'yandex':
                return $baseUrl . '/sahmatka/yandex_feedx.php';
            case 'avito':
                return $baseUrl . '/sahmatka/avito_feedx.php';
            default:
                return $baseUrl;
        }
    }
}
