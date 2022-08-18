<?php

namespace skewer\build\Page\Sitemap;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Visible;
use skewer\base\site_module;
use skewer\components\auth\Auth;

/**
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    /** @var string шаблон карты сайта */
    public $template = 'tree.php';

    public function init()
    {
        $this->setParser(parserPHP);
    }

    /**
     * Выполнение модуля.
     *
     * @return int
     */
    public function execute()
    {
        /** @var array исключаем раздел, но берём подразделы */
        $aExcluded = [\Yii::$app->sections->topMenu(), \Yii::$app->sections->leftMenu()];

        /** @var array запрещенные политикой разделы */
        $aDenySections = Auth::getDenySections('public') ?: [];

        /** @var array исключаем разделы с подразделами */
        $aExcludedWithChild = array_merge($aDenySections, [\Yii::$app->sections->serviceMenu(), \Yii::$app->sections->tools()]);

        /** @var array исключаем подразделы, но сам разделы берём */
        $aExcludedChild = [\Yii::$app->sections->main()];

        $this->setData('aAllSections', self::forest(\Yii::$app->sections->getValue('lang_root'), $this->getAllSections(), $aExcluded, $aExcludedWithChild, $aExcludedChild));
        $this->setTemplate($this->template);

        return psComplete;
    }

    // func

    /**
     * Вернёт список всех разделов, сгруппированный по полю parent и отсортированный в пределах группы.
     *
     * @return array
     */
    public function getAllSections()
    {
        $aOut = [];

        $aAllSections = TreeSection::find()
            ->orderBy(['parent' => SORT_ASC, 'position' => SORT_ASC])
            ->asArray()->all();

        foreach ($aAllSections as $item) {
            $aOut[$item['parent']][] = [
                'id' => $item['id'],
                'parent' => $item['parent'],
                'title' => $item['title'],
                'href' => '[' . $item['id'] . ']',
                'link' => $item['link'],
                'bActiveLink' => ($item['visible'] != Visible::HIDDEN_FROM_PATH),
                'visible' => $item['visible'],
            ];
        }

        return $aOut;
    }

    /**
     * Вернет "Лес" разделов.
     *
     * @param $section - стартовый раздел
     * @param $list    - общий список разделов, сгруппированный по полю `parent` и отсортированный в нужном порядке
     * @param array $aExcluded           - разделы исключенные из вывода. (но их подразделы в вывод попадают)
     * @param array $aExcludedWithChild  - разделы исключенные из вывода вместе с подразделами
     * @param array $aExcludedChild      - разделы, подразделы которых в вывод не включаются, но сам раздел берём
     *
     * @return array
     */
    public static function forest($section, $list, $aExcluded = [], $aExcludedWithChild = [], $aExcludedChild = [])
    {
        if (!isset($list[$section])) {
            return [];
        }

        $out = [];

        foreach ($list[$section] as $data) {
            // Раздел, исключенный с детьми не включаем в итоговый массив
            if (in_array($data['id'], $aExcludedWithChild)) {
                continue;
            }
            if (in_array($data['id'], $aExcluded) || $data['visible'] == Visible::HIDDEN_NO_INDEX) {
                // Исключенные и скрытые от индексации разделы иключаем, а их подразделы выводим на уровень выше
                $out += self::forest($data['id'], $list, $aExcluded, $aExcludedWithChild, $aExcludedChild);
            } elseif (in_array($data['id'], $aExcludedChild)) {
                $data['children'] = [];
                $out[$data['id']] = $data;
            } else {
                $data['children'] = self::forest($data['id'], $list, $aExcluded, $aExcludedWithChild, $aExcludedChild);
                $out[$data['id']] = $data;
            }
        }

        return $out;
    }
}// class
