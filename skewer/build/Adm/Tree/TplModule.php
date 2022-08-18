<?php

namespace skewer\build\Adm\Tree;

/**
 * Класс для обработки ветки шаблонов
 * Class TplModule.
 */
class TplModule extends Module
{
    /** @var string заместитель основной JS библиотеки */
    protected $sMainJSClass = 'Tree4Tpl';

    /** @var bool Флаг наличия нескольких деревьев */
    protected $bMultiTree = true;

    /**
     * Отдает id родительского раздела.
     *
     * @return int
     */
    protected function getStartSection()
    {
        return (int) \Yii::$app->sections->templates();
    }

    /**
     * Возвращает заголовок дерева.
     *
     * @return bool|mixed|string
     */
    protected function getTreeTitle()
    {
        return \Yii::t('tree', 'tpl_tree_title');
    }
}
