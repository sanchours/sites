<?php

namespace skewer\build\Tool\Labels;

use skewer\build\Tool\Labels\models\Labels;
use skewer\components\config\installer\Api as ApiInstaller;

class LabelHelper
{
    public static function isInstallModuleLabel()
    {
        return (new ApiInstaller())
            ->isInstalled('Labels', 'Tool');
    }

    /**
     * Возвращает набор меток для замены.
     *
     * @return array
     */
    public static function getReplaceData()
    {
        $labels = Labels::getAll();

        /** @var Labels $label */
        foreach ($labels as $label) {
            $labelsForReplace['pattern'][] = '/\[\[' . $label->alias . '\]\]/i';
            $labelsForReplace['replaces'][] = $label->default;
        }

        return $labelsForReplace ?? null;
    }
}
