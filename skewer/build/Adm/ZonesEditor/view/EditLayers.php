<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.12.2016
 * Time: 12:41.
 */

namespace skewer\build\Adm\ZonesEditor\view;

use skewer\base\site\Layer;
use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\view\ListView;

class EditLayers extends ListView
{
    public $bManyLabelsCount;
    public $aOpenedGroups;
    public $aCurrentGroupsNames;
    public $aData;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('title', \Yii::t('ZonesEditor', 'module_title'), ['listColumns' => ['flex' => 1]])

            ->setGroups('groupTitle', true, $this->bManyLabelsCount, $this->aOpenedGroups)
            ->setHighlighting('name', '', $this->aCurrentGroupsNames, 'font-weight: bold')

            // Подсветка деактивированных модулей
            ->setHighlighting('useInZone', '', 'false', 'color: #999999')

            ->buttonRowCustomJs('ApproveBtn', Layer::ADM, 'ZonesEditor')
            ->buttonRowCustomJs('DeleteOrCloneBtn', Layer::ADM, 'ZonesEditor')

            ->buttonAddNew('editModule')
            ->buttonBack()
            ->buttonSeparator()
            ->buttonIf(CurrentAdmin::isSystemMode(), \Yii::t('ZonesEditor', 'add_zone'), 'addZoneForm', 'icon-configuration')

            ->enableDragAndDrop('SortLabels')

            ->setValue($this->aData)
            ->setModuleLangValues(
                [
                    'btnRow_deleteParams',
                    'btnRow_copyParams',
                    'btnRow_disableModule',
                    'btnRow_enableModule',
                ]
            );
    }
}
