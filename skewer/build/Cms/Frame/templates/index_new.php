<?php
require RELEASEPATH . '/libs/ant-design-pro/Asset.php';
$bundle = $this->getAssetManager()->getBundle(\skewer\libs\AntDesignPro\Asset::className());
$baseUrl = $this->getAssetManager()->getBundle(\skewer\libs\AntDesignPro\Asset::className())->baseUrl;

\skewer\build\Cms\Frame\AssetForReactAdmin::register($this);

use skewer\base\site\Site;

$sTitlePage = Site::getSiteAdmDefaultTitle();

$this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="manifest" href="<?=$baseUrl; ?>/manifest.json">
    <link rel="stylesheet" href="<?=$baseUrl; ?>/umi.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script type="text/javascript">
        var titlePage = '<?= $sTitlePage; ?>';
        var lang = '';
        var dict = [];

        var rootPath = '<?= $bundle->baseUrl . '/js'; ?>';
        var ckedir = '';

        const buildConfig = {

          // версия движка
          cmsVersion: '4.0',

          // название слоя
          layerName: 'Cms',
          rootPath: rootPath,

          // имя основного файла для запросов
          request_script: '/admin/index.php',

          request_dir: '/admin/',

          // путь для вызова файлового браузера
          files_path: '/oldadmin/',

          // время ожидания ответа ajax запроса
          request_timeout: 300000, // 5 минут

          CKEditorLang: window.lang
        };

    </script>
    <script>
        window.publicPath = '<?=$baseUrl; ?>/';
    </script>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
    <title><?= $sTitlePage; ?></title>
    <link rel="icon" href="<?=$baseUrl; ?>/favicon.png" type="image/x-icon">
    <script>window.routerBase = "/";</script>
    <script async src="<?=$baseUrl; ?>/pwacompat.min.js"></script>
    <style>
        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Bold.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Bold.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Bold.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Bold.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Bold.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Bold.svg#SourceSansPro-Bold') format('svg');
            font-weight: bold;
            font-style: normal;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-LightItalic.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-LightItalic.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-LightItalic.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-LightItalic.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-LightItalic.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-LightItalic.svg#SourceSansPro-LightItalic') format('svg');
            font-weight: 300;
            font-style: italic;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBoldItalic.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBoldItalic.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBoldItalic.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBoldItalic.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBoldItalic.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBoldItalic.svg#SourceSansPro-SemiBoldItalic') format('svg');
            font-weight: 600;
            font-style: italic;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLightItalic.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLightItalic.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLightItalic.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLightItalic.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLightItalic.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLightItalic.svg#SourceSansPro-ExtraLightItalic') format('svg');
            font-weight: 200;
            font-style: italic;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Regular.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Regular.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Regular.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Regular.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Regular.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Regular.svg#SourceSansPro-Regular') format('svg');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-BoldItalic.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-BoldItalic.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BoldItalic.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BoldItalic.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BoldItalic.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BoldItalic.svg#SourceSansPro-BoldItalic') format('svg');
            font-weight: bold;
            font-style: italic;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLight.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLight.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLight.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLight.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLight.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-ExtraLight.svg#SourceSansPro-ExtraLight') format('svg');
            font-weight: 200;
            font-style: normal;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBold.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBold.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBold.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBold.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBold.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-SemiBold.svg#SourceSansPro-SemiBold') format('svg');
            font-weight: 600;
            font-style: normal;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-BlackItalic.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-BlackItalic.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BlackItalic.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BlackItalic.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BlackItalic.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-BlackItalic.svg#SourceSansPro-BlackItalic') format('svg');
            font-weight: 900;
            font-style: italic;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Light.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Light.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Light.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Light.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Light.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Light.svg#SourceSansPro-Light') format('svg');
            font-weight: 300;
            font-style: normal;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Italic.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Italic.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Italic.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Italic.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Italic.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Italic.svg#SourceSansPro-Italic') format('svg');
            font-weight: normal;
            font-style: italic;
        }

        @font-face {
            font-family: 'Source Sans Pro';
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Black.eot');
            src: url('<?=$baseUrl; ?>/fonts/SourceSansPro-Black.eot?#iefix') format('embedded-opentype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Black.woff2') format('woff2'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Black.woff') format('woff'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Black.ttf') format('truetype'),
            url('<?=$baseUrl; ?>/fonts/SourceSansPro-Black.svg#SourceSansPro-Black') format('svg');
            font-weight: 900;
            font-style: normal;
        }
    </style>
    <?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
<noscript>Sorry, we need js to run correctly!</noscript>
<div id="root"></div>
<script src="<?=$baseUrl; ?>/umi.js"></script>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
