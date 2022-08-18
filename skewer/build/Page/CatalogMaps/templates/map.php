<?php

use skewer\build\Page\CatalogMaps;

/*
 * @var $this \yii\web\View
 * @var array $settings
 * @var string $MarkerClusterAssetUrl
 */

?>

<?php
    if (CatalogMaps\Api::getActiveProvider() == CatalogMaps\Api::providerYandexMap) {
        CatalogMaps\Assets\AssetYandexMap::register(\Yii::$app->view);
    } elseif (CatalogMaps\Api::getActiveProvider() == CatalogMaps\Api::providerGoogleMap) {
        CatalogMaps\Assets\AssetGoogleMap::register(\Yii::$app->view);
    }

?>

<div class="js_map_container">
    <div class="js_map_settings" style="display: none;"><?=$settings; ?></div>
    <div class="b-maps js_maps">
</div>

</div>
<div class="js_asset_url" style="display: none;"><?=$MarkerClusterAssetUrl; ?></div>
