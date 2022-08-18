<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 15.05.2018
 * Time: 9:53.
 */

namespace skewer\build\Tool\SEOTemplates\view;

use skewer\base\site\Type;
use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\view\ListView;

class TemplatesList extends ListView
{
    /** @var array */
    public $data;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        // добавляем поля
        $this->_list
            //->addField( 'id', 'id', 'i', 'hide', array('listColumns' => array('width' => 40)) )
            ->field('fullAlias', \Yii::t('SEO', 'fullAlias'), 'string', ['listColumns' => ['width' => 200]])
            ->field('name', \Yii::t('SEO', 'name'), 'string', ['listColumns' => ['flex' => 1]])
            // добавляем данные
            ->setValue($this->data)
            // элементы управления
            ->buttonRowUpdate('editForm')
            ->buttonIf(Type::hasCatalogModule(), \Yii::t('SEO', 'clone'), 'cloneForm', 'icon-clone');

        if (CurrentAdmin::isSystemMode()) {
            $this->_list->buttonRowDelete('delete');
        }
    }
}
