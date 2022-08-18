<?php

namespace skewer\build\Page\Menu;

use skewer\base\section;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site_module;
use skewer\base\SysVar;
use yii\helpers\ArrayHelper;

class Module extends site_module\page\ModulePrototype
{
    public $template = 'leftMenu.twig';
    public $parentSection = 3;
    public $customSections = '';
    public $openAll = '';

    /*
     * Набор модулей, контент коготрых будет проброшен внутрь модуля
     */
    /** @var string Набор пробрасываемых модулей */
    public $subModules = '';

    private static $icons = [];
    private static $targetBlank = [];

    public function execute()
    {
        // собираем набор модулей, которые должны отдать контент текущему
        foreach (explode(',', $this->subModules) as $sSubModuleName) {
            $sSubModuleName = trim($sSubModuleName);
            if ($sSubModuleName) {
                // запрашиваем процесс из этой метки
                $oProcess = \Yii::$app->processList->getProcess('out.' . $sSubModuleName, psAll);

                if (!$oProcess) {
                    continue;
                }

                // ждем, если есть незаконченные
                if ($oProcess->getStatus() !== psComplete) {
                    return psWait;
                }

                // рендерим его
                $oProcess->render();

                // добавляем данные на вывод
                $this->setData($sSubModuleName, $oProcess->getOuterText());
            }
        }

        $to = $this->sectionId();
        $iMainSection = \Yii::$app->sections->main();
        $from = $this->parentSection;

        $mode = 'normal';
        if ($this->menuHideChilds($this->parentSection)) {
            $this->openAll = 0;
            $to = 0;
        }
        if ($this->openAll == 1) {
            $mode = 'openAll';
        }
        if ($this->openAll == 2) {
            $mode = 'openSecond';
        }
        if ($this->customSections) {
            $mode = 'custom';
        }

        $items = [];

        switch ($mode) {
            case 'normal':
                $items = Tree::getUserSectionTree($from, $to, 1);
                break;
            case 'openAll':
                $items = Tree::getUserSectionTree($from, $to);
                break;
            case 'openSecond':
                $items = Tree::getUserSectionTree($from, $to, 2);
                break;

            case 'custom':
                $list = explode(',', $this->customSections);
                $sections = Tree::getCachedSection();
                $items = [];
                foreach ($list as $id) {
                    if (isset($sections[$id])) {
                        $section = $sections[$id];
                        $section['show'] = in_array($section['visible'], section\Visible::$aShowInMenu);
                        $section['href'] = $section['link'] ?: '[' . $id . ']';
                        $section['selected'] = ($id == $to);
                        $items[] = $section;
                    }
                }

                break;
        } // switch

        $this->setData('items', $items);
        $this->setData('aTargetBlank', self::getTargetBlank());

        if (SysVar::get('Menu.ShowIcons')) {
            $this->setData('icon', self::getIcons());
        }

        if ($iMainSection != $to) {
            $this->setData('hideMenu', 1);
        }
        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * Иконки для разделов.
     *
     * @return array
     */
    private static function getIcons()
    {
        if (!self::$icons) {
            self::$icons = Parameters::getList()
                ->asArray()
                ->fields(['value', 'parent'])
                ->group(Parameters::settings)
                ->name('category_icon')
                ->get();

            self::$icons = ArrayHelper::map(self::$icons, 'parent', 'value');

            self::$icons = array_filter(self::$icons, static function ($s) {
                return (bool) $s;
            });
        }

        return self::$icons;
    }

    /**
     * Иконки для разделов.
     *
     * @return array
     */
    private static function getTargetBlank()
    {
        if (!self::$targetBlank) {
            self::$targetBlank = Parameters::getList()
                ->asArray()
                ->fields(['parent'])
                ->group(Parameters::settings)
                ->name('_target_blank')
                ->get();

            self::$targetBlank = ArrayHelper::map(self::$targetBlank, 'parent', 'parent');
        }

        return self::$targetBlank;
    }

    /**
     * Скрывать дочерние элементы для меню из заданного раздела.
     *
     * @param int $iParentSection Секция родитель для меню
     *
     * @return false|string
     */
    private function menuHideChilds($iParentSection)
    {
        return Parameters::getValByName($iParentSection, Parameters::settings, 'menuHideChild');
    }
}
