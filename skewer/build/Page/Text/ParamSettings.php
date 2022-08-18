<?php

namespace skewer\build\Page\Text;

use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\base\section\Template;
use skewer\build\Adm\Tree\Search;
use skewer\build\Design\Zones;
use skewer\components\search\models\SearchIndex;
use skewer\components\seo\Service;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends \skewer\build\Adm\ParamSettings\Prototype
{
    /**
     * Имя параметра, определяющего
     * порядок вывода параметров в редакторе админки.
     */
    const FIELD_ORDER = 'field_order';

    /** {@inheritdoc} */
    public function getList()
    {
        return [];
    }

    /** {@inheritdoc} */
    public function saveData()
    {
    }

    public function getInstallationParam()
    {
        return [
            [
                'name' => 'base',
                'title' => \Yii::t('text', 'titleModule'),
                'parameters' => [
                    [
                        'name' => Zones\Api::OWNER,
                        'value' => Zones\Api::USER_OWNER,
                        'access_level' => Type::paramSystem,
                    ],
                    [
                        'name' => Parameters::object,
                        'value' => Module::getNameModule(),
                        'access_level' => Type::paramSystem,
                    ],

                    [
                        'name' => Parameters::template,
                        'value' => 'staticContent.twig',
                        'access_level' => Type::paramSystem,
                    ],

                    [
                        'name' => Parameters::layout,
                        'value' => 'content',
                        'access_level' => Type::paramSystem,
                    ],

                    [
                        'name' => Parameters::titleName,
                        'value' => $this->sLabelTitle,
                        'access_level' => Type::paramSystem,
                    ],

                    [
                        'name' => 'source',
                        'value' => '500:b-editor',
                        'title' => $this->sLabelTitle,
                        'access_level' => Type::paramWyswyg,
                    ],
                ],
            ],
        ];
    }

    public function install($sSubType = 'base')
    {
        $aSections = parent::install($sSubType);
        $this->addFieldOrder($aSections);

        return $aSections;
    }

    public function copy()
    {
        parent::copy();

        $aSections = Template::getSubSectionsByTemplate($this->iParent);
        $aSections[] = $this->iParent;
        $this->addFieldOrder($aSections);
    }

    public function delete()
    {
        $aSections = parent::delete();

        // Удалить запись об этом модуле из поля field_order
        $sParam = $this->sGroupName . ':source;';

        foreach ($aSections as $item) {
            if (!($sVal = Parameters::getShowValByName($item, Parameters::settings, self::FIELD_ORDER))) {
                $sVal = Parameters::getShowValByName($item, Parameters::settings, self::FIELD_ORDER, true);
            }

            Parameters::setParams($item, Parameters::settings, self::FIELD_ORDER, null, str_replace($sParam, '', $sVal));
        }

        // Сбросить поиск для разделов, в которых удалили группу
        if ($aSections) {
            $oSearch = new Search();

            SearchIndex::updateAll(['status' => 0], ['class_name' => $oSearch->getName(), 'object_id' => $aSections]);
            Service::updateSearchIndex();
        }

        return $aSections;
    }

    /**
     * Добавляем запись о модуле в параметр сортировки.
     *
     * @param array $aSections массив с id разделов
     */
    protected function addFieldOrder($aSections)
    {
        // Обновить параметр field_order
        $sParam = $this->sGroupName . ':source;';

        foreach ($aSections as $item) {
            if (!($sVal = Parameters::getShowValByName($item, Parameters::settings, self::FIELD_ORDER))) {
                $sVal = Parameters::getShowValByName($item, Parameters::settings, self::FIELD_ORDER, true);
            }

            Parameters::setParams($item, Parameters::settings, self::FIELD_ORDER, null, $sVal . $sParam);
        }
    }
}
