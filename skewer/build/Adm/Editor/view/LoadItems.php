<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 22.02.2017
 * Time: 12:25.
 */

namespace skewer\build\Adm\Editor\view;

use skewer\components\ext\view\FormView;
use skewer\components\seo\Api;

class LoadItems extends FormView
{
    public $aFieldsData;

    /** @var array|false данные для ссылки на клиентскую часть */
    public $aLink;

    public $oSeo;
    public $aExcludedFields;

    public $iSectionId;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if ($this->aLink) {
            $this->_form->fieldLink('page_href', \Yii::t('editor', 'hyperlink'), $this->aLink['sTextLink'], $this->aLink['sHrefLink'], ['cls' => 'sk-section-hyperlink']);
        }

        if ($this->aFieldsData) {
            foreach ($this->aFieldsData as $aFieldData) {
                $this->_form->field(
                    $aFieldData['name'],
                    $aFieldData['title'],
                    $aFieldData['editorType'],
                    $aFieldData['params']
                );
            }
        }

        if ($this->oSeo) {
            Api::appendExtForm($this->_form, $this->oSeo, $this->iSectionId, $this->aExcludedFields);
        }

        $this->_form->useSpecSectionForImages($this->iSectionId);
        $this->_form
            ->buttonSave()
            ->buttonCancel();
    }
}
