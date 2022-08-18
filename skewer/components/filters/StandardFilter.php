<?php

namespace skewer\components\filters;

/**
 * Class StandardFilter - стандартный(не индексируемый поиск.системами фильтр).
 */
class StandardFilter extends FilterPrototype
{
    protected function initData($sFilterConditions)
    {
        // Стандартный фильтр(неиндексируемый) инициализируется get-параметрами
        $aData = \Yii::$app->request->get();
        $aData = $this->canonizeToArrayFormat($aData);
        $this->data = $this->filteringInputValues($aData);
    }

    /**
     * Получить disallow правила фильтра для Robots.txt.
     *
     * @return array
     */
    public function getRobotsDisallowPatterns()
    {
        $aResult = [];

        // Запрещаем все поля, используемые в фильтре
        foreach ($this->aFilterFields as $oFilterField) {
            $aResult[$oFilterField->getFieldName()] = '/*' . $oFilterField->getFieldName() . '=';
        }

        return $aResult;
    }
}
