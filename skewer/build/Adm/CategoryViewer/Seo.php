<?php

namespace skewer\build\Adm\CategoryViewer;

use skewer\components\seo\SeoPrototype;

class Seo extends SeoPrototype
{
    /**
     * Вёрнёт название сущности.
     *
     * @return string
     */
    public static function getTitleEntity()
    {
        return 'Элемент разводки';
    }

    /**
     * Вернет группу в таблице seo_data(поле group).
     *
     * @return string
     */
    public static function getGroup()
    {
        return '';
    }

    /**
     * Вернёт статическую часть псевдонима шаблона.
     *
     * @return string
     */
    public static function getAlias()
    {
        return 'categoryViewerElement';
    }

    /**
     * Метод запрашивает данные соответсвующей сущности
     * и сохраняет их во внутреннею переменную.
     *
     * @return mixed
     */
    public function loadDataEntity()
    {
    }

    /**
     * Метод собирает с сущности метки для замены в seo шаблонах.
     *
     * @param array $aParams - параметры для подстановки
     *
     * @return mixed
     */
    public function extractReplaceLabels($aParams)
    {
        return [];
    }

    /**
     * Возвращает имя поискового класса, соответствующее данному seo компоненту.
     *
     * @return string
     */
    protected function getSearchClassName()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function editableSeoTemplateFields()
    {
        return [
            'nameImage',
            'altTitle',
        ];
    }
}
