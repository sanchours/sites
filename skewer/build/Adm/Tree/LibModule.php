<?php

namespace skewer\build\Adm\Tree;

use skewer\base\section\Tree;

/**
 * Класс для обработки ветки библиотек
 * Class LibModule.
 */
class LibModule extends Module
{
    /** @var string заместитель основной JS библиотеки */
    protected $sMainJSClass = 'Tree4Lib';

    /** @var bool Флаг наличия нескольких деревьев */
    protected $bMultiTree = true;

    /**
     * Отдает id родительского раздела.
     *
     * @return int
     */
    protected function getStartSection()
    {
        return (int) \Yii::$app->sections->library();
    }

    /**
     * Устанавливаем список шаблонов для библиотек.
     *
     * @return array
     */
    protected function getTemplateList()
    {
        $aResult = [];

        // добавляем фиктивный раздел Папка
        $aResult[] = [
                 'id' => Tree::tplDirId,
                 'title' => '"' . \Yii::t('tree', 'folder') . '"',
         ];

        return $aResult;
    }

    /**
     * Возвращает заголовок дерева.
     *
     * @return bool|mixed|string
     */
    protected function getTreeTitle()
    {
        return \Yii::t('tree', 'lib_tree_title');
    }
}
