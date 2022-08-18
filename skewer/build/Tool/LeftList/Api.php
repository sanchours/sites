<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 11.08.2016
 * Time: 15:02.
 */

namespace skewer\build\Tool\LeftList;

use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\components\auth\CurrentAdmin;
use skewer\components\search\CmsSearchEvent;

class Api
{
    /**
     * Returns the fully qualified name of this class.
     *
     * @return string the fully qualified name of this class
     */
    public static function className()
    {
        return get_called_class();
    }

    public static function search(CmsSearchEvent $oSearchEvent)
    {
        $query = $oSearchEvent->query;

        $aModuleList = \Yii::$app->register->getModuleList(Layer::TOOL);

        $i = 0;

        foreach ($aModuleList as $sModuleName) {
            if ($i > $oSearchEvent->limit) {
                continue;
            }

            $oModuleConfig = \Yii::$app->register->getModuleConfig($sModuleName, Layer::TOOL);

            if (mb_stripos($oModuleConfig->getName(), $query) !== false or
                mb_stripos($oModuleConfig->getTitle(), $query) !== false) {
                // если нет доступа и пользователь не системный, то пропустить
                if (!CurrentAdmin::canUsedModule($oModuleConfig->getName()) and !CurrentAdmin::isSystemMode()) {
                    continue;
                }

                ++$i;
                $oSearchEvent->addRow([
                    'title' => sprintf(
                        '%s: %s',
                        \Yii::$app->register->getModuleConfig('LeftList', Layer::TOOL)->getTitle(),
                        $oModuleConfig->getTitle()
                        ),
                    'url' => Site::admUrl($oModuleConfig->getName()),
                ]);
            }
        }
    }
}
