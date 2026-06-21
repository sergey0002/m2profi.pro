<?
header('Access-Control-Allow-Origin: *'); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0", false);
header("Cache-Control: max-age=0", false);
header("Pragma: no-cache");
  
// Те же контроллеры и экшены без шаблона 
include('config.php');
require_once __DIR__ . '/inc/compred_helpers.php';
$_GET=$_REQUEST;

$compred_page_meta = null;
if (($_GET['ctr'] ?? '') === 'compred' && ($_GET['act'] ?? '') === 'public') {
    $compred_page_meta = compred_bootstrap_public_meta((string)($_GET['token'] ?? ''));
}

if( $_SESSION['sh_login'] || 1==1 )
{
?>
  <html lang="ru">

  <head>
    <meta charset="utf-8">
    <?php if ($compred_page_meta): ?>
    <?php include __DIR__ . '/fw/templates/compred/_public_meta.php'; ?>
    <?php else: ?>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="googlebot" content="noindex, nofollow" />
    <meta name="yandex" content="none" />
    <title>M2 Profi</title>
    <meta name="description" content="">
    <?php endif; ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/sahmatka/template/default/images/favicon/favicon.png">
    <link rel="shortcut icon" href="/sahmatka/template/default/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/sahmatka/template/default/images/favicon/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/sahmatka/template/default/images/favicon/apple-touch-icon-72x72.png">
    <meta property="og:image" content="/sahmatka/template/default/images/home-og.jpg">
    <link rel="stylesheet" href="/sahmatka/template/default/libs/air-datepicker/css/datepicker.min.css">
    <link rel="stylesheet" href="/sahmatka/template/default/libs/formstyler/jquery.formstyler.css">
    <style>
      .jq-checkbox {
        margin-right: 0;
      }
    </style>
    <link rel="stylesheet" href="/sahmatka/template/default/libs/aos/aos.css">
    <link rel="stylesheet" href="/sahmatka/template/default/libs/slick/slick.css">
    <link rel="stylesheet" href="/sahmatka/template/default/css/style.css">
    <link rel="stylesheet" href="/sahmatka/template/default/css/media.css">
    <script src="/sahmatka/template/default/libs/jquery-3.3.1/jquery-3.3.1.min.js"></script>

    <!--Капча и обработка форм -->
    <script src="/captcha/formprotect.js"></script>
    <link rel="stylesheet" href="/captcha/style.css">

    <script src="/sahmatka/template/default/libs/formstyler/jquery.formstyler.min.js"></script>

    <script src="/sahmatka/template/default/js/myfw_iframe.js"></script>

    <link href="/sahmatka/template/fonts/montserrat/montserrat.css" rel="stylesheet" />
    <link href="/sahmatka/template/fonts/exo2/exotwo.css" rel="stylesheet">

    <link rel="stylesheet" href="/sahmatka/template/default/libs/slick/slick.css">
    <link rel="stylesheet" href="/sahmatka/template/default/libs/slick/slick-theme.css">

    <script src="/sahmatka/template/default/libs/slick/slick.min.js"></script>

    <link rel="stylesheet" href="/sahmatka/template/default/css/admin.css">
    <link rel="stylesheet" href="/sahmatka/template/default/css/iframe.css">

    <link rel="stylesheet" href="/wiget_rent.css">

  </head>

  <body style="padding-top: 0;">

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
      (function(m, e, t, r, i, k, a) {
        m[i] = m[i] || function() {
          (m[i].a = m[i].a || []).push(arguments)
        };
        m[i].l = 1 * new Date();
        for (var j = 0; j < document.scripts.length; j++) {
          if (document.scripts[j].src === r) {
            return;
          }
        }
        k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
      })(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
      ym(18713149, "init", {
        clickmap: true,
        trackLinks: true,
        accurateTrackBounce: true,
        webvisor: true,
        trackHash: true
      });
    </script>
    <noscript>
      <div><img src="https://mc.yandex.ru/watch/18713149" style="position:absolute; left:-9999px;" alt="" /></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->

    <center>
      <div style="display:inline-block;  width:98%; font-size:16px; text-align:left;">
        <?	
		 include('router.php');
		?>
      </div>

  </body>

  </html>
  <?
}
 
// print '<pre>';
// print_r($log);
// print '</pre>';

?>