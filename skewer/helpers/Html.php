<?php

namespace skewer\helpers;

/**
 * Класс для работы с html кодом
 * Class Html.
 */
class Html
{
    /**
     * Отпределяет если ли контент в заданном тексте - срезает тэги и смотрит на оставшееся
     * также вырезает непечатные символы \xE2\x80\x8B, которые добавляет стандартый html редактор ExtJS.
     *
     * @param string $sText
     *
     * @return bool
     */
    public static function hasContent($sText)
    {
        return (bool) str_replace(["\r", "\n", '&nbsp;', ' '], '', trim(strip_tags($sText, '<img>'), " \t\n\r\0\x0B\xE2\x80\x8B"));
    }

    /**
     * Функция заменяет вхождения длинных подстрок с пробельными символами
     * на одиночные символы.
     *
     * @param string $text
     *
     * @return string
     */
    public static function replaceLongSpaces($text)
    {
        return preg_replace('/[ ]+/i', ' ', $text);
    }
}
