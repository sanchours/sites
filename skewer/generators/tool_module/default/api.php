<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\tool_module\Generator
 */
    $moduleName = $generator->moduleName;
    $nameAR = $generator->nameAR;
    $languageCategory = mb_strtolower($nameAR);
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Tool\\' . $moduleName;
    echo "<?php\n";
?>

namespace <?= $ns; ?>;

use skewer\build\Tool\<?= $moduleName; ?>\models;
use skewer\build\Tool\<?= $moduleName; ?>\view;


/**
 *  Class Api
 * @package skewer\build\Tool\<?= $moduleName . "\n"; ?>
 */
class Api{

    const FIELD_SORT = 'priority';

    /**
    * @param $sNameModel
    * @return int
    * @throws \yii\db\Exception
    */
    public static function getMaxPriority($sNameModel){

        $aLastPriority = \Yii::$app->getDb()->createCommand('
            SELECT MAX(`'.self::FIELD_SORT.'`)
            FROM ' . $sNameModel
            )->query()->read();

        $aLastPriority = (int)reset($aLastPriority) + 1;

        return $aLastPriority;
    }

    /**
    * @param $oItem
    * @param $iItemId
    * @param $iItemTargetId
    * @param $sPosition
    * @return bool
    */
    public static function sortItems($oItem, $iItemId, $iItemTargetId, $sPosition) {
        return \skewer\base\ui\Api::sortObjects($iItemId, $iItemTargetId, $oItem, $sPosition, '', 'id', self::FIELD_SORT);
    }

}