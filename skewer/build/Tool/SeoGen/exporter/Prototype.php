<?php

namespace skewer\build\Tool\SeoGen\exporter;

use skewer\base\ui\builder\FormBuilder;
use skewer\build\Tool\SeoGen\Api as SeoGenApi;
use skewer\components\excelHelpers;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

abstract class Prototype
{
    /** @var array Разделы-источники. Разделы из которых будет производиться экспорт */
    public $aSourceSections = [];

    /**
     * Получить разделы-источники.
     *
     * @return array
     */
    public function getSourceSections()
    {
        return $this->aSourceSections;
    }

    public function initParams($aParams)
    {
        $sInputSections = $aParams['sectionId'];

        $aInputSections = StringHelper::explode($sInputSections, ',', true, true);

        $aExportedSections = array_merge(SeoGenApi::getAllSubSections($aInputSections), $aInputSections);

        $aExportedSections = array_unique($aExportedSections);

        // Чтобы разделы выгрузились в том порядке в котором указаны в админке, в дереве разделов - сортируем их
        $aAllSections = [];
        SeoGenApi::collectSection(\Yii::$app->sections->root(), $aAllSections);

        // пересечение всех с теми которые нужно выгрузить
        $this->aSourceSections = array_intersect($aAllSections, $aExportedSections);

        // переиндексируем
        $this->aSourceSections = array_values($this->aSourceSections);
    }

    /**
     * Получить очередную порцию данных.
     *
     * @param int $iCurrentSectionId - id текущего раздела
     * @param int $iRowIndexEntity - Индекс записи в пределах сущности
     *
     * @return array|bool - вернет массив seo данных или false, если запись не найдена
     */
    public function getChunkData($iCurrentSectionId = 0, $iRowIndexEntity = 0)
    {
        if (!($aData = $this->getRecordWithinEntityByPosition($iCurrentSectionId, $iRowIndexEntity))) {
            return false;
        }

        $aRow = self::getBlankExportStructure();

        $aRow['type'] = ArrayHelper::getValue($aData, 'type', '');
        $aRow['url'] = ArrayHelper::getValue($aData, 'url', '');

        $aRow['title'] = [
            'value' => ArrayHelper::getValue($aData, 'seo.title.value', ''),
            'style' => (!ArrayHelper::getValue($aData, 'seo.title.overriden', true)) ? excelHelpers\Styles::$GREEN : false,
        ];

        $aRow['description'] = [
            'value' => ArrayHelper::getValue($aData, 'seo.description.value', ''),
            'style' => (!ArrayHelper::getValue($aData, 'seo.description.overriden', true)) ? excelHelpers\Styles::$GREEN : false,
        ];

        $aRow['keywords'] = [
            'value' => ArrayHelper::getValue($aData, 'seo.keywords.value', ''),
            'style' => (!ArrayHelper::getValue($aData, 'seo.keywords.overriden', true)) ? excelHelpers\Styles::$GREEN : false,
        ];

        return $aRow;
    }

    /**
     * Выгружаемые поля.
     *
     * @return array
     */
    public function fields4Export()
    {
        return [
            'type' => [
                'value' => 'Type',
                'width' => 20,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'url' => [
                'value' => 'URL',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'title' => [
                'value' => 'TITLE',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'description' => [
                'value' => 'DESCRIPTION',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
            'keywords' => [
                'value' => 'KEYWORDS',
                'width' => 65,
                'style' => excelHelpers\Styles::$HEADER,
            ],
        ];
    }

    /** Вернет массив с ключами выгружаемых данных */
    public function getBlankExportStructure()
    {
        return array_fill_keys(array_keys($this->fields4Export()), '');
    }

    /**
     * Метод вернет $iPosition запись сущности
     * Позиция записи вычисляется относительно общего отсортированного списка сущностей данного типа
     * Пример, News/Seo::getRecordWithinEntityByPosition(3) вернет 4ю новость опред.раздела(id раздела берется из seo класса).
     *
     * @param $iSectionId - позиция записи. Отсчет ведется с нуля
     * @param $iPosition - позиция записи. Отсчет ведется с нуля
     *
     * @return array | bool - массив с данными Ar сущности и SEO данными. false - если запись не найдена
     */
    public function getRecordWithinEntityByPosition(/* @noinspection PhpUnusedParameterInspection */$iSectionId,
        /* @noinspection PhpUnusedParameterInspection */
        $iPosition
    ) {
        return false;
    }

    /**
     * Валидация параметров экспорта.
     *
     * @param array $aParams - параметры эеспорта
     * @param array $aErrors - ошибка
     *
     * @return bool
     */
    public function validateParams($aParams, &$aErrors = [])
    {
        try {
            $sSectionList = ArrayHelper::getValue($aParams, 'sectionId', '');

            if (!$sSectionList) {
                throw new \Exception('Не задан id выгружаемого раздела');
            }
        } catch (\Exception $e) {
            $aErrors[] = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Получить namespace класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Получить псевдоним класса.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Получить название выгружаемой сущности.
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Построит поля для формы в адм.слое.
     *
     * @param FormBuilder $oForm
     */
    public function buildFieldInForm(FormBuilder $oForm)
    {
        $oForm->fieldString(
            'sectionId',
            'Введите Id раздела',
            ['subtext' => 'Раздел или список разделов, указанных через запятую']
        );
    }

    /**
     * Проверит соответствует ли шаблон раздела выгружаемым сущностям
     *
     * @param int $iSectionId
     *
     * @return bool
     */
    abstract public function checkTemplateSection($iSectionId);
}
