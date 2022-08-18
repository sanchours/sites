<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.03.2017
 * Time: 11:56.
 */

namespace skewer\components\GalleryOnPage;

use skewer\base\SysVar;

/**
 * Class FilePrototype.
 */
abstract class FilePrototype extends Prototype
{
    public static function getDir()
    {
        return __DIR__;
    }

    abstract public function getName();

    /**
     * Отдает имя режима.
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'FilePrototype';
    }

    public static function className()
    {
        return get_called_class();
    }

    /**
     * Отдает настройки для режима.
     *
     * @param bool $bSeeInParents - искать настройки у родителей
     *
     * @return mixed
     */
    public function getSettings($bSeeInParents = true)
    {
        $mData = $this->getData();

        if (!$mData) {
            return $this->getDefaultValues();
        }

        /*Если для этой сущности есть данные в БД отдадим их*/
        if (isset($mData[$this->getEntityName()]) && $mData[$this->getEntityName()]) {
            return $mData[$this->getEntityName()];
        }

        if ($bSeeInParents) {
            /*Попробуем получить данные о сущности родителя*/
            $sParentClass = get_parent_class(Api::getClassNameByEntity($this->getEntityName()));
            $sParentEntity = Api::getEntityByClassName($sParentClass);

            if (isset($mData[$sParentEntity]) && $mData[$sParentEntity]) {
                // Параметры родителя
                $aParentData = $mData[$sParentEntity];

                // Параметры которые можно перекрывать
                $aOverrideData = array_diff_key($aParentData, array_combine(static::excludedParams(), static::excludedParams()));

                //Дефолтные параметры
                $aDefaultValues = $this->getDefaultValues();

                //Перекрываем дефолтные параметры родительскими
                $aOverridenData = array_merge($aDefaultValues, $aOverrideData);

                return $aOverridenData;
            }

            /*Попробуем получить данные о сущности родителя родителя*/
            $sParentClass = get_parent_class(Api::getClassNameByEntity($sParentEntity));
            $sParentEntity = Api::getEntityByClassName($sParentClass);

            if (isset($mData[$sParentEntity]) && $mData[$sParentEntity]) {
                // Параметры родителя
                $aParentData = $mData[$sParentEntity];

                // Параметры которые можно перекрывать
                $aOverrideData = array_diff_key($aParentData, array_combine(static::excludedParams(), static::excludedParams()));

                //Дефолтные параметры
                $aDefaultValues = $this->getDefaultValues();

                //Перекрываем дефолтные параметры родительскими
                $aOverridenData = array_merge($aDefaultValues, $aOverrideData);

                return $aOverridenData;
            }
        }

        return $this->getDefaultValues();
    }

    /**
     * Отдает данные из БД с настройками.
     *
     * @return bool|mixed
     */
    public function getData()
    {
        $aData = json_decode(SysVar::get('CarouselData'), true);

        if (!$aData || $aData === null) {
            return false;
        }

        return $aData;
    }

    /**
     * Дефолтные значения.
     *
     * @return array
     */
    protected function getDefaultValues()
    {
        return [
            'items' => $this->iCountItems,
            'slideBy' => 'page',
            'margin' => 20,
            'nav' => true,
            'dots' => false,
            'autoWidth' => false,
            'responsive' => [
                0 => ['items' => 1],
                768 => ['items' => 2],
                980 => ['items' => 3],
                1240 => ['items' => 4],
            ],
            'loop' => false,
            'shadow' => false,
        ];
    }

    /** Исключенные из редактирования в админке параметры */
    public static function excludedParams()
    {
        return [
            'autoWidth',
        ];
    }
}
