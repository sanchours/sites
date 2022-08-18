<?php

namespace app\skewer\console;

use skewer\components\catalog\Dict;
use skewer\helpers\Transliterate;

/**
 * Контроллер для транслитерации значений.
 */
class TransliterateController extends Prototype
{
    /**
     * Для всех справочников вычисляет alias.
     *
     * @return bool
     */
    public function actionDictItems()
    {
        $aDicts = Dict::getDictionaries('Catalog');

        foreach ($aDicts as $oDict) {
            $oMagicTable = \skewer\base\ft\Cache::getMagicTable($oDict->id);

            $oQuery = $oMagicTable->find();

            while ($row = $oQuery->each()) {
                $sNewAlias = $row->alias
                    ? self::buildAlias($row->alias)
                    : self::buildAlias($row->title);

                $row->alias = $sNewAlias;
                $row->save();
            }
        }
    }

    /**
     * Построит транслитерацию.
     *
     * @param string $aAliasOrTitle
     *
     * @return string
     */
    private static function buildAlias($aAliasOrTitle)
    {
        $sTmpAlias = Transliterate::change($aAliasOrTitle);
        $sTmpAlias = Transliterate::changeDeprecated($sTmpAlias);
        $sTmpAlias = Transliterate::mergeDelimiters($sTmpAlias);
        $sTmpAlias = trim($sTmpAlias, '-');

        return $sTmpAlias;
    }
}
