<?php

namespace skewer\build\Page\PathLine;

use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\base\site\Site;
use skewer\base\site_module;

/**
 * Модуль вывода "хлебных крошек" на страницы.
 */
class Module extends site_module\page\ModulePrototype
{
    /** @var string Набор конечных разделов */
    public $stopSections = '';

    /** @var bool Флаг вывода ссылки на главную страницу */
    public $withMain = false;

    /** @var string шаблон хлебных крошек */
    public $template = 'pathLine.twig';

    public function execute()
    {
        $stopSections = explode(',', $this->stopSections);

        /*
         * Принудительно добавим в стоп-массив все известные менюшки
         */
        $stopSections[] = \Yii::$app->sections->topMenu();
        $stopSections[] = \Yii::$app->sections->leftMenu();
        $stopSections[] = \Yii::$app->sections->serviceMenu();
        $stopSections[] = \Yii::$app->sections->tools();

        $to = $this->sectionId();
        $from = \Yii::$app->sections->root();
        $mainSectionId = \Yii::$app->sections->main();

        // выбираем все закешированные разделы
        $sections = Tree::getCachedSection();

        // собираем путь из разделов
        $out = [];
        $id = $to;

        // хвост пришедший из других модулей
        if ($tailList = $this->getEnvParam('pathline_additem')) {
            foreach ($tailList as $tail) {
                $tail['href'] = Site::httpDomain() . $tail['href'];
                $out[] = $tail;
            }
        }

        /** Id разделов, которые не должны попасть в строку навигации */
        $aStoppedIds = \Yii::$app->sections->getValues('lang_root');
        $aStoppedIds[] = $mainSectionId;

        // последовательно собираем разделы
        while (isset($sections[$id]) && $id != $from) {
            $section = $sections[$id];

            if (in_array($id, $aStoppedIds) or ($section['visible'] == Visible::HIDDEN_FROM_PATH)) {
                $id = $section['parent'];
                continue;
            }

            if (in_array($id, $stopSections)) {
                break;
            }

            $section['href'] = self::buildHref($section);
            $section['selected'] = $id == $to && !$tailList;

            array_unshift($out, $section);
            $id = $section['parent'];
        }

        // Ссылка на главный раздел
        if ($this->withMain && count($out) && isset($sections[$mainSectionId])) {
            $section = $sections[$mainSectionId];
            $section['href'] = self::buildHref($section);
            $section['selected'] = $id == $to && !$tailList;

            $this->setData('main_page', $section);
        }

        if ($this->withMain || count($out) > 1) {
            $this->setData('items', $out);
        }

        $this->setTemplate($this->template);

        return psComplete;
    }

    /** {@inheritdoc} */
    public function canHaveContent()
    {
        return false;
    }

    /**
     * @param array $aSection
     *
     * @return string
     */
    public static function buildHref($aSection)
    {
        $sHref = $aSection['link'] ? \Yii::$app->router->rewriteURL($aSection['link']) : $aSection['alias_path'];
        $sHref = $sHref ?: '/';
        $sHref = trim(Site::httpDomain() . $sHref, '/') . '/';

        return $sHref;
    }
}
