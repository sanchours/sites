<?php

namespace skewer\build\Tool\ImportContent;

use skewer\build\Adm\Articles;
use skewer\build\Adm\FAQ;
use skewer\build\Adm\Gallery;
use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\build\Adm\News;

class Api
{
    /** @const Тип данных - разделы */
    const DATATYPE_NEWS = 'News';
    /** @const Тип данных - разделы */
    const DATATYPE_ARTICLES = 'Articles';
    /** @const Тип данных - разделы */
    const DATATYPE_REVIEWS = 'GuestBook';

    /** @const Директория хранения файла экспорта */
    const SEO_DIRECTORY = 'seo';

    /** @const Имя файла-образца для импорта */
    const IMPORT_BLANK_FILE_NEWS = 'importBlankNews.xlsx';
    /** @const Имя файла-образца для импорта */
    const IMPORT_BLANK_FILE_REVIEWS = 'importBlankReviews.xlsx';
    /** @const Имя файла-образца для импорта */
    const IMPORT_BLANK_FILE_ARTICLES = 'importBlankArticles.xlsx';

    /**
     * Вернёт массив сущностей. Формат [ тех.имя => название сущности ].
     *
     * @return array
     */
    public static function getEntities()
    {
        return [
          Articles\Seo::className() => 'Статья',
          News\Seo::className() => 'Новость',
          Gallery\Seo::className() => 'Альбом',
          FAQ\Seo::className() => 'Вопрос-ответ',
          GuestBook::className() => 'Отзыв',
        ];
    }

    public static function getDataTypes()
    {
        $aDataTypes = [Api::DATATYPE_NEWS => 'Новости',
                       Api::DATATYPE_ARTICLES => 'Статьи',
                       Api::DATATYPE_REVIEWS => 'Отзывы', ];

        return $aDataTypes;
    }

    /**
     * Путь к файлу импорта для web интерфейса.
     *
     * @param mixed $title
     *
     * @return string
     */
    public static function getWebPathImportBlankFile($title)
    {
        return '/local/?ctrl=ImportContent&&mode=import&&fileName=' . $title;
    }
}
