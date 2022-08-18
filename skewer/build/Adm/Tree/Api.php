<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 08.08.2016
 * Time: 15:57.
 */

namespace skewer\build\Adm\Tree;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\components\search\CmsSearchEvent;

class Api
{
    public static function search(CmsSearchEvent $oSearchEvent)
    {
        $query = $oSearchEvent->query;

        /* Поиск по пути */
        $path = '';

        if (is_numeric($query)) {
            $path = $query;
        } elseif (filter_var($query, FILTER_VALIDATE_URL) !== false) {
            $path = parse_url($query, PHP_URL_PATH);
        } elseif (preg_match('/^\/[\w_\/-]+\/$/', $query)) {
            $path = $query;
        }

        if ($path) {
            if (is_numeric($path)) {
                $id = $path;
            } else {
                $id = Tree::getSectionByPath($path);
            }
            $section = $id ? Tree::getSection($id) : null;

            if ($section and in_array(\Yii::$app->sections->root(), Tree::getSectionParents($section->id))) {
                $oSearchEvent->addRow([
                    'title' => sprintf(
                        '%s: %s [%d]',
                        \Yii::$app->register->getModuleConfig('Tree', Layer::ADM)->getTitle(),
                        self::makePath($section),
                        $section->id
                    ),
                    'url' => Site::admTreeUrl($section->id, 'editor'),
                ]);

                return;
            }
        }

        /*
         * Выбор по названию / псавдониму
         */
        $aList = TreeSection::find()
            ->where(['or',
                ['like', 'title', $oSearchEvent->query],
                ['like', 'alias', $oSearchEvent->query],
                ['like', 'alias_path', $oSearchEvent->query],
            ])
            ->limit($oSearchEvent->limit)
            ->all();

        /** @var TreeSection $section */
        foreach ($aList as $section) {
            // обрабатываем только наследников 3 раздела - ветку "Разделы"
            if (!in_array(\Yii::$app->sections->root(), Tree::getSectionParents($section->id))) {
                continue;
            }

            $oSearchEvent->addRow([
                'title' => sprintf(
                    '%s: %s [%d]',
                    \Yii::$app->register->getModuleConfig('Tree', Layer::ADM)->getTitle(),
                    self::makePath($section),
                    $section->id
                ),
                'url' => Site::admTreeUrl($section->id, 'editor'),
            ]);
        }
    }

    /**
     * Строит путь до раздела в виде строки.
     *
     * @param TreeSection $section
     *
     * @return string
     */
    private static function makePath(TreeSection $section)
    {
        $to = $section->id;
        $from = 0;

        // выбираем все закешированные разделы
        $sections = Tree::getCachedSection();

        // собираем путь из разделов
        $out = [];
        $id = $to;

        // последовательно собираем разделы
        while (isset($sections[$id]) && $id != $from) {
            $section = $sections[$id];

            if ($id != \Yii::$app->sections->root()) {
                array_unshift($out, $section['title']);
            }

            $id = $section['parent'];
        }

        return implode(' > ', $out);
    }

    /**
     * Returns the fully qualified name of this class.
     *
     * @return string the fully qualified name of this class
     */
    public static function className()
    {
        return get_called_class();
    }
}
