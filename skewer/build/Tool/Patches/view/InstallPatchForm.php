<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 13:44.
 */

namespace skewer\build\Tool\Patches\view;

use skewer\components\ext\view\FormView;

class InstallPatchForm extends FormView
{
    public $aVal;
    public $bDescriptionNotEmpty;
    public $bIsNotInstalled;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('patch_file', \Yii::t('patches', 'patch_file'), 's')
            ->field('patch_uid', \Yii::t('patches', 'patch_id'), 'show')
            ->field('status', \Yii::t('patches', 'status'), 'show')
            ->fieldIf($this->bDescriptionNotEmpty, 'description', \Yii::t('patches', 'description'), 'show', [])
            ->setValue($this->aVal)

            ->buttonIf( /* Патч не устанавливали - разрешаем ставить */
               $this->bIsNotInstalled,
                \Yii::t('patches', 'install'),
                'installPatch',
                'icon-install',
                'allow_do',
                ['actionText' => \Yii::t('patches', 'installText')]
            )
            ->buttonCancel('List')
            ->buttonSeparator('->');
    }
}
