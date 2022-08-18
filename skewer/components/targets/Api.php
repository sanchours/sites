<?php

namespace skewer\components\targets;

use skewer\components\targets\models\Targets;
use skewer\components\targets\models\TargetSelectors;
use skewer\components\targets\types\Prototype;
use yii\helpers\ArrayHelper;

class Api
{
    /**
     * Проверяет наобходимо ли пересобрать файл с целями
     * и отправляет на пересоздание.
     *
     * @param $asset
     * @param $basePath
     *
     * @return string
     */
    public static function convert($asset, $basePath)
    {
        $pos = mb_strrpos($asset, '.');

        if ($pos !== false) {
            if (!file_exists($basePath)) {
                mkdir($basePath);
            }

            $ext = mb_substr($asset, $pos + 1);

            if ($ext == 'js') {
                if (!file_exists($basePath . '/' . $ext)) {
                    mkdir($basePath . '/' . $ext);
                }

                $fileName = mb_substr($asset, 0, mb_strlen($asset) - 4);
                $newFileName = $fileName . '.compile.js';
                $fullFileName = $basePath . '/' . $newFileName;

                // перестраиваем если файла нет или нужно обновить его
                if (!is_file($fullFileName) or \Yii::$app->assetManager->forceCopy) {
                    self::createScript($fullFileName);
                }

                return $newFileName;
            }
        }

        return $asset;
    }

    /**
     * Пересоздает файл с целями.
     *
     * @param $fullFileName
     */
    public static function createScript($fullFileName)
    {
        $aSelectors = TargetSelectors::find()
            ->asArray()
            ->all();

        $aTargets = Targets::find()
            ->all();

        $aTargets = ArrayHelper::index($aTargets, 'name');

        $aOut['items'] = [];

        foreach ($aSelectors as &$item) {
            /** @var Prototype $oType */
            $oType = Creator::getObject(ucfirst($item['type']), true);

            if (!isset($aOut['items'][$item['selector']])) {
                $aOut['items'][$item['selector']] = [];
            }

            if ($item['name']) {
                $aOut['items'][$item['selector']][] = $oType::getTarget($aTargets[$item['name']]);
            }
        }

        $sData = \Yii::$app->getView()->renderFile(__DIR__ . '/templates/targets.php', $aOut);
        $h = fopen($fullFileName, 'w');
        fwrite($h, $sData);
        fclose($h);
    }

    public static function checkDuplicate($sName)
    {
        $aTarget = Targets::find()
            ->where(['name' => $sName])
            ->one();

        return $aTarget !== null;
    }
}
