
<?php

/**
 * @var \yii\web\View
 * @var $settings array -  настройки карты
 * @var $markers array  - маркеры
 * @var $capabilities string - возможности карты
 * @var $showSearchLine bool - выводить строку поиска
 * @var $showSetMarkerForm bool - выводить форму установки маркера
 */
use skewer\build\Page\CatalogMaps;

?>

<div class="b-map" id="js_map"></div>
<div id="js_settings" style="display: none;"><?php if (!empty($settings)): ?><?=$settings; ?><?php endif; ?></div>
<div id="js_marker" style="display: none;"><?php if (!empty($markers)): ?><?=$markers; ?><?php endif; ?></div>
<div id="js_capabilities" style="display: none;"><?=$capabilities; ?></div>

<?php if ($showSearchLine && (CatalogMaps\Api::getActiveProvider() == CatalogMaps\Api::providerGoogleMap)) :?>
    <input id="js_input_search" class="controls" type="text" placeholder="<?= Yii::t('editorMap', 'address_or_object'); ?>">
<?php endif; ?>
<div class="b-setmarker">
	<form id="js_map_form" method="post">
		<input type="hidden" name="lat"    id="js_map_lat">
		<input type="hidden" name="lng"    id="js_map_lng">
		<input type="hidden" name="address" id="js_map_address">
		<input type="hidden" name="zoom"   id="js_map_zoom">
		<input type="hidden" name="center" id="js_map_center">
		<input type="hidden" name="mode" value="editorMap" />
		<input type="hidden" name="cmd" value="save" />
		<button id="js_map_save" type="submit"><?= Yii::t('editorMap', 'save'); ?></button>
	</form>
</div>
<?php if ($showSetMarkerForm): ?>
    <?= $this->renderPhpFile(__DIR__ . DIRECTORY_SEPARATOR . 'formSetMarker.php'); ?>
<?php endif; ?>
 