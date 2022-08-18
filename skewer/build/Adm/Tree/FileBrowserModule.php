<?php

namespace skewer\build\Adm\Tree;

/**
 * Дерево разеделов для панели выбора файлов
 * Class FileBrowserModule.
 */
class FileBrowserModule extends Module
{
    /** @var string заместитель основной JS библиотеки */
    protected $sMainJSClass = 'Tree2FileBrowser';

    /**
     * Возвращает заголовок дерева.
     *
     * @return bool|mixed|string
     */
    protected function getTreeTitle()
    {
        return \Yii::t('tree', 'file_tree_title');
    }

    /**
     * Отдает id родительского раздела.
     *
     * @return int
     */
    protected function getStartSection()
    {
        return 0;
    }
}
