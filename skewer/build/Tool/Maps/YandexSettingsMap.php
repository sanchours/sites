<?php

namespace skewer\build\Tool\Maps;

use skewer\base\ft\Editor;
use skewer\build\Page\CatalogMaps\models\GeoObjects;
use skewer\build\Page\CatalogMaps\models\Maps;
use yii\base\Model;
use skewer\base\ui\builder\FormBuilder;

/**
 * Class YandexSettingsMap для работ с яндекс картами
 * @package skewer\build\Tool\Maps
 */
class YandexSettingsMap extends Model
{
    /** @var GeoObjects $geoObject */
    private $geoObject;

    private static $languageCategory = 'Maps';

    /** настройки для Yandex */
    public $coordinates;
    public $center;
    public $zoom;
    public $address;

    public function rules()
    {
        return [
            [['coordinates', 'center', 'zoom', 'address'], 'safe']
        ];
    }

    public function __construct(
        int $geoObjectId = null,
        $config = []
    ) {
        parent::__construct($config);

        $this->setGeoObjData($geoObjectId);
    }

    private function setGeoObjData(int $geoObjectId = null)
    {
        if (is_null($geoObjectId)) {
            $this->geoObject = new GeoObjects();
            $this->geoObject->map = new Maps();
        } else {
            $this->geoObject = GeoObjects::findOne($geoObjectId);
            $this->setAttributesByGeoObject();
        }
    }

    private function setAttributesByGeoObject()
    {
        $this->coordinates = implode(',', [
            $this->geoObject->latitude,
            $this->geoObject->longitude
        ]);
        $this->center = $this->geoObject->map->center;
        $this->zoom = $this->geoObject->map->zoom;
        $this->address = $this->geoObject->address;
    }
    /**
     * @throws \Exception
     */
    public function save()
    {
        if ($this->geoObject === null) {
            $this->geoObject = new GeoObjects();
            $this->geoObject->map = new Maps();
        }

        if (!strstr($this->center, ',')) {
            $label = $this->geoObject->map->getAttributeLabel('center');
            throw new \Exception(
                "Необходимо указать две координаты через запятую для поля «{$label}»"
            );
        }

        if (!strstr($this->coordinates, ',')) {
            $label = \Yii::t(self::$languageCategory, 'coordinates');
            throw new \Exception(
                "Необходимо указать две координаты через запятую для поля «{$label}»"
            );
        }

        $this->geoObject->map->center = $this->center;
        $this->geoObject->map->zoom = $this->zoom;
        if (!$this->geoObject->map->save()) {
            throw new \Exception(
                current($this->geoObject->map->getFirstErrors())
            );
        }

        $this->geoObject->address = $this->address;

        list(
            $this->geoObject->latitude,
            $this->geoObject->longitude
            ) = explode(',', $this->coordinates);

        $this->geoObject->map_id = $this->geoObject->map->id;
        if (!$this->geoObject->save()) {
            throw new \Exception(
                current($this->geoObject->getFirstErrors())
            );
        }

        return $this->geoObject->id;
    }

    public function getGeoObject(): GeoObjects
    {
        return $this->geoObject;
    }

    public static function setFormView(FormBuilder &$form, string $prefix = '', array $params = [])
    {
        foreach (self::getSettingsMainField() as $field) {
            $form
                ->field("{$prefix}{$field['name']}", $field['title'], $field['editorType'],
                    $field['params'] + $params);
        }
    }

    public static function getSettingsMainField(): array
    {
        return [
            [
                'name' => 'coordinates',
                'title' => \Yii::t(self::$languageCategory, 'coordinates'),
                'editorType' => Editor::STRING,
                'params' => ['subtext' => \Yii::t(self::$languageCategory, 'determination_of_coordinates')],
            ],
            [
                'name' => 'zoom',
                'title' => \Yii::t(self::$languageCategory, 'zoom'),
                'editorType' => Editor::INTEGER,
                'params' => [],
            ],[
                'name' => 'center',
                'title' => \Yii::t(self::$languageCategory, 'center'),
                'editorType' => Editor::STRING,
                'params' => [],
            ],[
                'name' => 'address',
                'title' => \Yii::t(self::$languageCategory, 'address'),
                'editorType' => Editor::STRING,
                'params' => [],
            ],

        ];
    }

}
