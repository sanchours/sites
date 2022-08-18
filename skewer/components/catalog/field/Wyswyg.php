<?php

namespace skewer\components\catalog\field;

use skewer\base\orm\state\StateSelect;
use skewer\components\filters\FilteredInterface;
use skewer\components\filters\widgets;
use yii\helpers\ArrayHelper;

class Wyswyg extends Prototype implements FilteredInterface
{
    protected function build($value, $rowId, $aParams)
    {
        $value = trim($value);

        $html = ($value) ? $this->getHtmlData($value) : '';

        return [
            'value' => $value,
            'tab' => $value,
            'html' => $html,
        ];
    }

    public function addFilterConditionToQuery(StateSelect $oQuery, $aFilterData = [])
    {
        $value = ArrayHelper::getValue($aFilterData, $this->getName(), []);

        $value = reset($value);

        $value = self::replaceHtmlEntities($value);

        if ($value) {
            $sName = $this->getName();
            $oQuery->like($sName, $value);

            return true;
        }

        return false;
    }

    public function getFilterWidgetName()
    {
        return widgets\Input::getTypeWidget();
    }

    /**
     * Заменяет html сущности.
     *
     * @param string $value
     *
     * @return string
     */
    private static function replaceHtmlEntities($value)
    {
        $value = htmlentities($value, ENT_QUOTES | ENT_HTML401);

        // Одинарная кавычка указана в ckeditor указывается кодом &#39
        // а функция htmlentities меняет её на '&#039'
        $value = str_replace('&#039', '&#39', $value);

        return $value;
    }
}
