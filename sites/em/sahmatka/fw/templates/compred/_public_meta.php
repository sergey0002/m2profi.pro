<?php
/** Open Graph / Twitter / JSON-LD для публичной страницы compred. */
if (empty($compred_page_meta) || !is_array($compred_page_meta)) {
    return;
}
$m = $compred_page_meta;
?>
<title><?= htmlspecialchars($m['title']) ?> — M2 Profi</title>
<meta name="description" content="<?= htmlspecialchars($m['description']) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="M2 Profi">
<meta property="og:title" content="<?= htmlspecialchars($m['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($m['description']) ?>">
<meta property="og:url" content="<?= htmlspecialchars($m['url']) ?>">
<meta property="og:image" content="<?= htmlspecialchars($m['image']) ?>">
<meta property="og:locale" content="ru_RU">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($m['title']) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($m['description']) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($m['image']) ?>">
<link rel="canonical" href="<?= htmlspecialchars($m['url']) ?>">
<script type="application/ld+json"><?= json_encode($m['json_ld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
