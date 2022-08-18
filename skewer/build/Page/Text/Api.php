<?php

namespace skewer\build\Page\Text;

use skewer\base\section\Parameters;
use skewer\build\Design\Zones;
use yii\helpers\ArrayHelper;

class Api
{
    /** Функция сбора контента с текстовых блоков в выбранной зоне
     * @param $iPageId - id страницы
     * @param string $sZone - название зоны
     *
     * @return string - собранный текст
     */
    public static function getTextContentFromZone($iPageId, $sZone = 'content')
    {
        $sText = '';

        $aParams = Parameters::getList($iPageId)
            ->name(Parameters::object)
            ->value(Module::getNameModule())
            ->groups()
            ->asArray()->rec()->get();

        if (!$iZoneId = Zones\Api::getZoneIdByName($sZone, $iPageId)) {
            return '';
        }

        // Список используемых в данной зоне меток
        $aLabels = Zones\Api::getLabelList($iZoneId, $iPageId);

        // Отбрасываем ненужные
        $aGroupWithTextModule = array_intersect(ArrayHelper::getColumn($aLabels, 'name'), array_keys($aParams));

        if ($aGroupWithTextModule) {
            $aGroup = Parameters::getList($iPageId)
                ->group($aGroupWithTextModule)
                ->groups()
                ->asArray()->rec()->get();

            foreach ($aGroup as $sGroupName => $aParameters) {
                $aParameters = ArrayHelper::index($aParameters, 'name');

                if (ArrayHelper::getValue($aParameters, Parameters::layout . '.value') !== $sZone) {
                    continue;
                }

                $sText .= ' ' . ArrayHelper::getValue($aParameters, 'source.show_val', '');
            }
        }

        return $sText;
    }
}
