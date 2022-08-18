<?php

namespace skewer\base\section\params;

/**
 * Класс, содержащий сведения о типах параметров
 * Class Type.
 */
class Type
{
    /** Системный параметр */
    const paramSystem = 0;

    /** Строка */
    const paramString = 1;

    /** Текстовое поле */
    const paramText = 2;

    /** Редактор */
    const paramWyswyg = 3;

    /** Картинка */
    const paramImage = 4;

    /** Галочка */
    const paramCheck = 5;

    /** Файл */
    const paramFile = 6;

    /** HTML */
    const paramHTML = 7;

    /** Выпадающий список */
    const paramSelect = 9;

    /** Целое число */
    const paramInt = 10;

    /** Число с плавающей точкой */
    const paramFloat = 11;

    /** Дата */
    const paramData = 15;

    /** Время */
    const paramTime = 16;

    /** Дата и время */
    const paramDataTime = 17;

    /** Наследуемый */
    const paramInherit = 20;

    /** Наследуемый от конкретного раздела */
    const paramInheritFromSection = 21;

    /** Системный раздел */
    const paramServiceSection = 23;

    /** Языковой параметр */
    const paramLanguage = 24;
    /** Текст HTML */
    const paramTextHTML = 31;

    /** Текст JS */
    const paramTextJS = 32;

    /** Текст Css */
    const paramTextCss = 33;

    /** Галерея */
    const paramGallery = 34;

    /** Мультисписок */
    const paramMultiSelect = 35;

    /** Карта(список маркеров)*/
    const paramMapListMarkers = 36;

    /**
     * Список типов полей, использующих расширенное значение.
     *
     * @return array
     */
    public static function getShowValFieldList()
    {
        return[
            self::paramText,
            self::paramWyswyg,
            self::paramHTML,
            self::paramTextHTML,
            self::paramTextJS,
            self::paramTextCss,
        ];
    }

    /**
     * Список параметров для метки.
     *
     * @return array
     */
    public static function getParametersList()
    {
        $aParams = [
            self::paramSystem => \Yii::t('params', 'type_system'),
            self::paramString => \Yii::t('params', 'type_string'),
            self::paramText => \Yii::t('params', 'type_text'),
            self::paramWyswyg => \Yii::t('params', 'type_wyswyg'),
            self::paramImage => \Yii::t('params', 'type_imagefile'),
            self::paramGallery => \Yii::t('params', 'type_gallery'),
            self::paramCheck => \Yii::t('params', 'type_check'),
            self::paramFile => \Yii::t('params', 'type_file'),
            self::paramHTML => \Yii::t('params', 'type_html'),
            self::paramSelect => \Yii::t('params', 'type_select'),
            self::paramMultiSelect => \Yii::t('params', 'type_multiselect'),
            self::paramMapListMarkers => \Yii::t('params', 'type_map'),
            self::paramInt => \Yii::t('params', 'type_int'),
            self::paramFloat => \Yii::t('params', 'type_float'),
            self::paramData => \Yii::t('params', 'type_date'),
            self::paramTime => \Yii::t('params', 'type_time'),
            self::paramDataTime => \Yii::t('params', 'type_datetime'),
            self::paramInherit => \Yii::t('params', 'type_inherit'),
            self::paramInheritFromSection => \Yii::t('params', 'type_inherit_from_section'),
            self::paramServiceSection => \Yii::t('params', 'type_service_section'),
            self::paramLanguage => \Yii::t('params', 'type_language'),
            self::paramTextHTML => \Yii::t('params', 'type_text_html'),
            self::paramTextJS => \Yii::t('params', 'type_text_js'),
            self::paramTextCss => \Yii::t('params', 'type_text_css'),

            -self::paramString => \Yii::t('params', 'type_string_local'),
            -self::paramText => \Yii::t('params', 'type_text_local'),
            -self::paramWyswyg => \Yii::t('params', 'type_wyswyg_local'),
            -self::paramImage => \Yii::t('params', 'type_imagefile_local'),
            -self::paramGallery => \Yii::t('params', 'type_gallery_local'),
            -self::paramCheck => \Yii::t('params', 'type_check_local'),
            -self::paramFile => \Yii::t('params', 'type_file_local'),
            -self::paramHTML => \Yii::t('params', 'type_html_local'),
            -self::paramSelect => \Yii::t('params', 'type_select_local'),
            -self::paramMapListMarkers => \Yii::t('params', 'type_map'),
            -self::paramMultiSelect => \Yii::t('params', 'type_multiselect_local'),
            -self::paramInt => \Yii::t('params', 'type_int_local'),
            -self::paramFloat => \Yii::t('params', 'type_float_local'),
            -self::paramData => \Yii::t('params', 'type_date_local'),
            -self::paramTime => \Yii::t('params', 'type_time_local'),
            -self::paramDataTime => \Yii::t('params', 'type_datetime_local'),
            -self::paramInherit => \Yii::t('params', 'type_inherit_local'), // такое бывает?
            -self::paramServiceSection => \Yii::t('params', 'type_service_section_local'),
            -self::paramLanguage => \Yii::t('params', 'type_language_local'),
            -self::paramTextHTML => \Yii::t('params', 'type_text_html_local'),
            -self::paramTextJS => \Yii::t('params', 'type_text_js_local'),
            -self::paramTextCss => \Yii::t('params', 'type_text_css_local'),
        ];

        foreach ($aParams as $key => &$sParam) {
            $sParam = '[' . $key . '] ' . $sParam;
        }

        return $aParams;
    }

    /**
     * @static возвращает массив с описаниями типов по access_level
     *
     * @return array
     */
    public static function getParamTypes()
    {
        return [
            self::paramString => ['type' => 'string', 'val' => 'value'],
            self::paramText => ['type' => 'text', 'val' => 'show_val'],
            self::paramWyswyg => ['type' => 'wyswyg', 'val' => 'show_val'],
            self::paramImage => ['type' => 'file', 'val' => 'value'],
            self::paramGallery => ['type' => 'gallery', 'val' => 'value'],
            self::paramCheck => ['type' => 'check', 'val' => 'value'],
            self::paramFile => ['type' => 'file', 'val' => 'value'],
            self::paramHTML => ['type' => 'html', 'val' => 'show_val'],
            self::paramSelect => ['type' => 'select', 'val' => 'value'],
            self::paramMultiSelect => ['type' => 'multiselect', 'val' => 'value'],
            self::paramMapListMarkers => ['type' => 'mapListMarkers', 'val' => 'value'],
            self::paramInt => ['type' => 'int', 'val' => 'value'],
            self::paramFloat => ['type' => 'float', 'val' => 'value'],
            self::paramData => ['type' => 'date', 'val' => 'value'],
            self::paramTime => ['type' => 'time', 'val' => 'value'],
            self::paramDataTime => ['type' => 'datetime', 'val' => 'value'],
            self::paramInherit => ['type' => 'inherit', 'val' => 'value'],
            self::paramServiceSection => ['type' => 'service_section', 'val' => 'value'],
            self::paramLanguage => ['type' => 'language', 'val' => 'value'],
            self::paramTextHTML => ['type' => 'text_html', 'val' => 'show_val'],
            self::paramTextJS => ['type' => 'text_js', 'val' => 'show_val'],
            self::paramTextCss => ['type' => 'text_css', 'val' => 'show_val'],
            self::paramInheritFromSection => ['type' => 'string', 'val' => 'value'],
        ];
    }

    /**
     * Проверяет, использует ли тип расширеное значение.
     *
     * @param $sType
     *
     * @return bool
     */
    public static function hasShowValUseType($sType)
    {
        return in_array($sType, ['text', 'wyswyg', 'html', 'text_html', 'text_js', 'text_css']);
    }
}
