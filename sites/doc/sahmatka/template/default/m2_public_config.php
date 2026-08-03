<?php
/**
 * Единый публичный конфиг тенанта (контракт как у sigma).
 * Подключать до любого script, который читает window.M2PROFI_CONFIG.
 */
if (!function_exists('m2_resolve_site_base_url')) {
	function m2_resolve_site_base_url(): string
	{
		if (!empty($GLOBALS['config']['base_url'])) {
			return rtrim((string)$GLOBALS['config']['base_url'], '/');
		}
		$env = getenv('APP_URL');
		if ($env) {
			return rtrim($env, '/');
		}
		if (function_exists('get_app_url')) {
			$u = get_app_url();
			if ($u) {
				return rtrim($u, '/');
			}
		}
		$sub = trim((string)($GLOBALS['config']['site_subdomain'] ?? 'doc'));
		return 'https://' . $sub . '.m2profi.pro';
	}
}

$m2SiteSubdomain = trim((string)($GLOBALS['config']['site_subdomain'] ?? 'doc'));
$m2SiteBaseUrl = m2_resolve_site_base_url();
$m2SahmatkaUrl = $m2SiteBaseUrl . '/sahmatka';
$m2AjaxRouterUrl = $m2SahmatkaUrl . '/ajax_router.php';
$m2IframeRouterUrl = $m2SahmatkaUrl . '/iframe_router.php';
$m2AjaxActionsUrl = $m2SahmatkaUrl . '/ajax_actions.php';
$m2ClientSiteUrl = (string)($GLOBALS['config']['client_site_url'] ?? '');

$m2PublicConfig = json_encode([
	'siteSubdomain' => $m2SiteSubdomain,
	'baseUrl' => $m2SiteBaseUrl,
	'sahmatkaUrl' => $m2SahmatkaUrl,
	'ajaxRouter' => $m2AjaxRouterUrl,
	'iframeRouter' => $m2IframeRouterUrl,
	'ajaxActions' => $m2AjaxActionsUrl,
	'assetsUrl' => $m2SiteBaseUrl,
	'clientSiteUrl' => $m2ClientSiteUrl,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<script>
window.M2PROFI_CONFIG = <?=$m2PublicConfig?>;
window.M2WidgetConfig = window.M2WidgetConfig || window.M2PROFI_CONFIG;
</script>
