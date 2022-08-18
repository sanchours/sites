<?php

namespace skewer\build\Adm\Tree;

/**
 * Класс для обработки основной ветки сайта
 * Class MainModule.
 */
class MainModule extends Module
{
    /** @var string заместитель основной JS библиотеки */
    protected $sMainJSClass = 'Tree4Main';

    /** @var bool Флаг наличия нескольких деревьев */
    protected $bMultiTree = true;

    /**
     * Отдает id родительского раздела.
     *
     * @return int
     */
    protected function getStartSection()
    {
        return (int) \Yii::$app->sections->root();
    }

    /**
     * Возвращает заголовок дерева.
     *
     * @return bool|mixed|string
     */
    protected function getTreeTitle()
    {
        return \Yii::t('tree', 'main_tree_title');
    }
}
