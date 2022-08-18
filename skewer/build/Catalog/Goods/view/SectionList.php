<?php

namespace skewer\build\Catalog\Goods\view;

use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\components\auth\CurrentAdmin;
use skewer\components\catalog;
use skewer\components\config\installer;
use skewer\components\ext;

/**
 * Построитель списка товарных позиций для раздела
 * Class SectionList.
 *
 * @var
 *
 * @property \skewer\build\Catalog\Goods\model\SectionList $model
 */
class SectionList extends ListPrototype
{
    public function build()
    {
        // собираем фильтр

        if (!$this->_module->sectionId()) {
            $this->addCustomFilter(
                'section',
                \Yii::t('catalog', 'section'),
                $this->model->getSection(),
                'SELECT',
                catalog\Section::getList()
            );
        }

        $this
            ->addFilter('article', $this->model->getFilter('article'))
            ->addFilter('title', $this->model->getFilter('title'))
            ->addFilter('price', $this->model->getFilter('price'))
            ->addFilter('active', $this->model->getFilter('active'), 'SELECT', [
                    1 => \Yii::t('catalog', 'yes'),
                    2 => \Yii::t('catalog', 'no'),
            ]);

        // собираем набор полей для таблицы
        $this
            ->addField('id', 'string', ['flex' => 1])
            ->addField('article', 'string', ['flex' => 3])
            ->addField('title', 'string', ['flex' => 3])
            ->addField('price', 'money', ['flex' => 1])
            ->addField('buy', 'check', ['flex' => 1, 'showAsDisabledEdit' => 1])
            ->addField('fastbuy', 'check', ['flex' => 1, 'showAsDisabledEdit' => 1])
            ->addField('active', 'check', ['flex' => 1, 'showAsDisabledEdit' => 1])
            ->addField('on_main', 'check', ['flex' => 1, 'showAsDisabledEdit' => 1])
            ->addField('hit', 'check', ['flex' => 1, 'showAsDisabledEdit' => 1])
            ->addField('new', 'check', ['flex' => 1, 'showAsDisabledEdit' => 1])
            ->addField('discount', 'check', ['flex' => 1, 'showAsDisabledEdit' => 1])
            ->addYandexExportField('in_yandex')
            ->setHighlighting('available_section', \Yii::t('catalog', 'error_no_main_section'));

        // устанавливаем редактируемые поля
        $this->setEditableFields($this->model->getEditableFields(['in_yandex']), 'edit');

        // разрешаем сортировку в разделах
        if ($this->isSection()) {
            $this->setSorting('sort');
        }

        // Вывод галочек для множественный операций
        $this->_list->showCheckboxSelection();

        // элементы управления
        $this
            ->btnAddGoods()
            ->btnTools()
            ->btnDelGoods();

        if (CurrentAdmin::isSystemMode() && (!$this->_module->sectionId())) {
            $this->btnDelAllGoods();
        }

        $this->btnRowModGoods()
            ->btnRowEdit('edit')
            ->btnRowSetFirst()
            ->btnRowClone();
    }

    /**
     * Кнопка клонировать.
     *
     * @param string $state
     *
     * @return $this
     */
    protected function btnRowClone($state = 'clone')
    {
        if ($this->isSection()) {
            $this->_list->buttonRow($state, \Yii::t('adm', 'clone'), 'icon-clone', $state);
        }

        return $this;
    }

    /**
     * Кнопка сортировки: товар переносится в начало списка.
     *
     * @param string $state
     *
     * @return $this
     */
    protected function btnRowSetFirst($state = 'setFirst')
    {
        if ($this->isSection()) {
            $this->_list->buttonRow($state, \Yii::t('adm', 'set_first'), 'icon-upgrade', $state);
        }

        return $this;
    }

    /**
     * Кнопка перехода к списку модификаций.
     *
     * @return $this
     */
    protected function btnRowModGoods()
    {
        if (SysVar::get('catalog.goods_modifications')) {
            $this->_list->buttonRowCustomJs(
                'ViewModificationsBtn',
                Layer::CATALOG,
                'Goods',
                ['tooltip' => \Yii::t('catalog', 'modificationsItems')]
            );
        }

        return $this;
    }

    /**
     * Кнопка добавления товара.
     *
     * @return $this
     */
    protected function btnAddGoods()
    {
        if ($this->isSection() && $this->isCard()) {
            $this->_list->buttonAddNew('edit');
        }

        return $this;
    }

    /**
     * Кнопка удаления товаров.
     *
     * @return $this
     */
    private function btnDelGoods()
    {
        $this->_list->buttonSeparator();
        $this->_list->buttonDeleteMultiple();

        return $this;
    }

    /**
     * Кнопка удаления всех товаров.
     *
     * @return $this
     */
    private function btnDelAllGoods()
    {
        $this->_list->buttonSeparator('->');
        $this->_list->buttonConfirm('deleteAll', \Yii::t('adm', 'del_all'), \Yii::t('adm', 'delAllGoods'), 'icon-delete');

        return $this;
    }

    /**
     * Кнопка перехода в настройки.
     *
     * @return $this
     */
    protected function btnTools()
    {
        // кнопка установки карточки для добавления товара
        if ($this->getLayer() == 'Adm') {
            $this->_list->buttonEdit('settings', \Yii::t('catalog', 'btn_settings'));
        } else {
            if ($this->isSection()) {
                $this->_list->buttonCustomExt(
                    ext\docked\Api::create(\Yii::t('catalog', 'card'))
                        ->setIconCls(ext\docked\Api::iconEdit)
                        ->setState('setCard')
                        ->setAction('setCard')
                        ->unsetDirtyChecker()
                );
            }
        }

        return $this;
    }

    /**
     * Добавление поля YandexExport.
     *
     * @param $sField
     *
     * @return $this
     */
    protected function addYandexExportField($sField)
    {
        $installer = new installer\Api();

        $oField = $this->model->getField($sField);

        $this->_list->fieldIf(
            $installer->isInstalled('YandexExport', \skewer\base\site\Layer::TOOL) && $oField && $oField->getAttr('active'),
            $sField,
            $oField ? $oField->getTitle() : '',
            'check',
            ['listColumns' => ['flex' => 1]]
        );

        return $this;
    }

    /**
     * Факт нахождения в разделе.
     *
     * @return bool
     */
    private function isSection()
    {
        return $this->_module->sectionId() || $this->model->getSection();
    }

    /**
     * Факт наличия привязанной карточки к разделу.
     *
     * @return bool
     */
    private function isCard()
    {
        $card = $this->_module->getCardName();

        return (bool) $card;
    }

    /**
     * Имя слоя вывода.
     *
     * @return mixed
     */
    private function getLayer()
    {
        return $this->_module->getLayerName();
    }
}
