<?php

namespace skewer\base\section;

use skewer\base\section\models\TreeSection;
use skewer\build\Page\Main;
use skewer\components\auth\Policy;
use skewer\components\seo;
use yii\helpers\ArrayHelper;

class Tree
{
    const typeSection = 0; /** тип - раздел */
    const typeDirectory = 1; /** тип - папка */
    const policyUser = 'user';
    const policyAdmin = 'admin';

    /** id для подстановки в набор шаблонов */
    const tplDirId = -1;

    /**
     * Лимит рекурсии.
     *
     * @var int
     */
    private static $iLevelLimit = 10;

    public static $AvailableSectionCache = [];
    protected static $copyes = [];

    /** @var bool Флаг очистки кэша разделов */
    private static $bClearCache = true;

    /**
     * Получение кешированного списка разделов.
     *
     * @param int $iId Id раздела
     * @param bool $bOnlyVisible Только видимый раздел
     *
     * @return array
     * использовать только для клиенской части
     */
    public static function getCachedSection($iId = 0, $bOnlyVisible = false)
    {
        static $aSections = null;

        if ($iId and $bOnlyVisible and !isset(self::getVisibleSections()[$iId])) {
            return [];
        }

        if (!$aSections or self::$bClearCache) {
            $sections = TreeSection::find();

            foreach ($sections->each() as $section) {
                $aSections[$section->id] = $section->getAttributes();
            }
        }
        self::$bClearCache = false;

        // Если раздел ещё не проиндексирован, то проиндексировать
        if ($iId and !isset($aSections[$iId])) {
            $aSections[$iId] = self::getSection($iId, true) ?: [];
            // Обновить кэш видимых разделов
            $aSections[$iId] and self::getVisibleSections(true);
        }

        return $iId ?
            ((isset($aSections[$iId]) and (!$bOnlyVisible or isset(self::getVisibleSections()[$iId]))) ? $aSections[$iId] : []) :
            ($bOnlyVisible ? array_intersect_key($aSections, self::getVisibleSections()) : $aSections);
    }

    /**
     * Возвращает массив id видимых и существующих разделов. Использует кэш.
     *
     * @param bool $bUpdateCache Обновить кэш?
     *
     * @return array
     */
    public static function &getVisibleSections($bUpdateCache = false)
    {
        static $aSectionsVisibleIds = null;

        if (($aSectionsVisibleIds === null) or $bUpdateCache) {
            $aSectionsVisibleIds = TreeSection::find()
                ->where(['visible' => Visible::$aOpenByLink])
                ->andWhere("link LIKE ''")
                ->indexBy('id')
                ->asArray()
                ->column();
        }

        return $aSectionsVisibleIds;
    }

    /**
     * Получение объекта раздела.
     *
     * @param int|string $mId Идентификатор (id/alias)
     * @param bool $bAsArray Флаг вывода результатов как массив, а не объект
     * @param bool $bOnlyVisible Только видимый раздел
     *
     * @return null|array|TreeSection
     */
    public static function getSection($mId, $bAsArray = false, $bOnlyVisible = false)
    {
        if (is_numeric($mId)) {
            $oQuery = TreeSection::find()->where(['id' => $mId]);
        } else {
            $oQuery = TreeSection::find()->where(['alias' => $mId]);
        }

        if ($bOnlyVisible) {
            $oQuery->andWhere(['visible' => Visible::$aOpenByLink, 'link' => '']);
        }

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->one();
    }

    /**
     * Получение набора объектов разделов.
     *
     * @param int[] $list Набор идентификаторов
     * @param bool $bAsArray Флаг вывода результатов как массив, а не объект
     * @param bool $bKeepOrder Использовать оригинальный порядок
     *
     * @return array
     */
    public static function getSections($list, $bAsArray = false, $bKeepOrder = false)
    {
        $out = [];
        $sections = TreeSection::findAll(['id' => $list]);

        foreach ($sections as $section) {
            $out[$section->id] = $bAsArray ? $section->getAttributes() : $section;
        }

        if ($bKeepOrder) {
            $realOut = [];
            foreach ($list as $key) {
                if (isset($out[$key])) {
                    $realOut[] = $out[$key];
                }
            }

            return $realOut;
        }

        return $out;
    }

    /**
     * @param $path
     * @param string $tail
     * @param array $denySections
     * @param bool $bMain - использовать в качестве условия окончания поиска раздела разбор урла до "/'
     *
     * @return int
     */
    public static function getSectionByPath($path, &$tail = '', $denySections = [], $bMain = true)
    {
        if (!$path) {
            return 0;
        }

        $curPath = $path;
        $id = $i = 0;

        do {
            $oQuery = TreeSection::find()
                ->where(
                    [
                    'alias_path' => $curPath,
                    'visible' => [
                        Visible::HIDDEN_FROM_MENU,
                        Visible::VISIBLE,
                        Visible::HIDDEN_NO_INDEX,
                    ], ]
            );

            if ($denySections) {
                $oQuery->andWhere(['NOT IN', 'id', $denySections]);
            }

            $row = $oQuery->one();

            if ($row) {
                $id = $row->id;
            } else {
                $curPath = (mb_strlen($curPath) >= 2) ? mb_substr($curPath, 0, mb_strrpos($curPath, '/', -2) + 1) : '/';
            }

            if ($bMain && ($curPath == '/')) {
                break;
            }
        } while (!$id and $curPath and ++$i < self::$iLevelLimit);

        $tail = ($curPath === $path) ? '' : mb_substr($path, mb_strlen($curPath));

        if ($curPath == '/') {
            $id = \Yii::$app->sections->main();
        }

        return $id;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public static function getSectionByParent($id)
    {
        $aSections = TreeSection::find()
            ->where(['parent' => $id])
            ->asArray()
            ->all();

        return (array) $aSections;
    }

    public static function getSectionByAlias($alias, $parent)
    {
        $row = TreeSection::findOne(['alias' => $alias, 'parent' => $parent]);

        return $row ? $row->id : null;
    }

    /**
     * Значение поля alias_path для раздела $id.
     *
     * @param int $id Идентификатор раздела
     * @param bool $bUseCache Флаг использования кешированных данных
     * @param bool $bUseLink Использовать разделы-ссылки
     * @param bool $bOnlyVisible Только видимый раздел
     *
     * @return string
     */
    public static function getSectionAliasPath($id, $bUseCache = false, $bUseLink = false, $bOnlyVisible = false)
    {
        if (!$id) {
            return '';
        }

        $aSection = $bUseCache ? self::getCachedSection($id, $bOnlyVisible) : self::getSection($id, true, $bOnlyVisible);
        if (!$aSection) {
            return '';
        }

        /** Массив для защиты от бесконечного цикла редиректов на разделы */
        $aRedirectIds = [];
        while ($bUseLink and $aSection['link']) {
            // Если редирект на раздел
            if (($iRedirectId = (int) trim($aSection['link'], '[]')) and
                 (($bUseCache and $aSectionRedirect = self::getCachedSection($iRedirectId, $bOnlyVisible)) or
                   (!$bUseCache and $aSectionRedirect = self::getSection($iRedirectId, true, $bOnlyVisible)))
               ) {
                // Если возникло зацикливание
                if (isset($aRedirectIds[$iRedirectId])) {
                    return $aSection['alias_path'];
                }

                $aRedirectIds[$iRedirectId] = 1;
                $aSection = $aSectionRedirect;
            } else {
                // Иначе редирект на url
                break;
            }
        }

        return ($bUseLink and $aSection['link']) ? $aSection['link'] : ($aSection['alias_path'] ?: '');
    }

    /**
     * Получить заголовок для раздела $id.
     *
     * @param int $id Идентификатор раздела
     * @param bool $bUseCache Флаг использования кешированных данных
     *
     * @return string
     */
    public static function getSectionTitle($id, $bUseCache = false)
    {
        if ($bUseCache) {
            $sections = self::getCachedSection();
            $val = ArrayHelper::getValue($sections, [$id, 'title']);
            if ($val !== null) {
                return $val;
            }
        }

        $row = TreeSection::findOne(['id' => $id]);

        return $row ? $row->title : '';
    }

    public static function getSectionParent($id, $bUseCache = false)
    {
        if ($bUseCache) {
            $sections = self::getCachedSection();
            $val = ArrayHelper::getValue($sections, [$id, 'parent']);
            if ($val !== null) {
                return $val;
            }
        }

        $row = TreeSection::findOne(['id' => $id]);

        return $row ? (int) $row->parent : 0;
    }

    /**
     * Вывод списка подразделов раздела.
     *
     * @param $iParent
     * @param bool $andParentToo выводить родительский раздел тоже
     * @param bool $bOnlyVisible выводить только видимые разделы
     *
     * @return array массив подразделов
     */
    public static function getAllSubsection($iParent, $andParentToo = false, $bOnlyVisible = false)
    {
        $aSections = [];
        $aParents = [$iParent];

        if ($andParentToo) {
            $aSections[] = $iParent;
        }

        do {
            $aNewParents = [];
            $bWhile = false;
            foreach (self::getCachedSection() as $key => $value) {
                if (isset($value['parent']) && in_array($value['parent'], $aParents)) {
                    // Если выводим только видимые разделы,
                    // и раздел скрыт из пути или скрыт от индексации, то пропускаем его
                    if ($bOnlyVisible && !in_array($value['visible'], Visible::$aOpenByLink)) {
                        continue;
                    }

                    $bWhile = true;
                    $aSections[$key] = $key;
                    $aNewParents[] = $key;
                }
            }
            $aParents = $aNewParents;
        } while ($bWhile);

        return $aSections;
    }

    /**
     * @param $id
     * @param bool $bAsArray
     * @param bool $bOnlyKeys
     *
     * @return array|TreeSection[]
     */
    public static function getSubSections($id, $bAsArray = false, $bOnlyKeys = false)
    {
        $list = TreeSection::find()->where(['parent' => $id])->orderBy('position')->all();

        if (!$bAsArray) {
            return $list;
        }

        $out = [];
        /** @var TreeSection $section */
        foreach ($list as $section) {
            if ($bOnlyKeys) {
                $out[] = $section->id;
            } else {
                $out[$section->id] = $section->getAttributes();
            }
        }

        return $out;
    }

    /**
     * Получение набора родительских разделов.
     *
     * @param int $id Идентификатор
     * @param int $stop Идентификатор стоп раздела
     * @param bool $bUseCache
     *
     * @return array
     */
    public static function getSectionParents($id, $stop = -1, $bUseCache = true)
    {
        $out = [];

        $curId = $id; // текущий обрабатываемый раздел

        do {
            // запрос родительского раздела
            $curId = self::getSectionParent($curId, $bUseCache);

            // выходим если достигли стоп-вершины
            if ($curId == $stop) {
                break;
            }

            // дополнение выходного массива
            if ($curId) {
                $out[] = $curId;
            }
        } while ($curId);

        return $out;
    }

    /**
     * Получение заголовка или списка заголовков страниц.
     *
     * @param int|int[] $id Иденификатор или список идентификаторов
     * @param bool $bWithSubs Флаг вывода подразделов
     * @param bool $bOnlyVisible Только видимые разделы?
     *
     * @return array|string
     */
    public static function getSectionsTitle($id, $bWithSubs = false, $bOnlyVisible = false)
    {
        if ($bWithSubs) {
            $out = [];
            $items = self::getSectionList($id);

            foreach ($items as $section) {
                if ($bOnlyVisible and (!in_array($section['visible'], Visible::$aOpenByLink) or $section['link'])) {
                    continue;
                }
                $out[$section['id']] = $section['title'];
            }

            return $out;
        }

        if (is_array($id)) {
            $out = [];
            $items = TreeSection::findAll(['id' => $id] + ($bOnlyVisible ? ['visible' => Visible::$aOpenByLink, 'link' => ''] : []));

            /** @var TreeSection $section */
            foreach ($items as $section) {
                $out[$section->id] = $section->title;
            }

            return $out;
        }

        $section = self::getSection($id);

        if ($bOnlyVisible and !$section->hasRealUrl()) {
            return '';
        }

        return $section ? $section->title : '';
    }

    /**
     * Получение подразделов в виде списка.
     *
     * @param int $id Идентификатор раздела корня дерева
     * @param bool|int|string $policy Политика доступа
     *
     * @return array|bool
     */
    public static function getSectionList($id, $policy = false)
    {
        $fullSections = Policy::getAvailableSections($policy, $id);

        if (isset($fullSections['.'])) {
            $out = [$fullSections['.']];
            $add = self::collect($id, $fullSections, 0, false);
            if ($add) {
                $out = array_merge($out, $add);
            }
        } else {
            $out = self::collect($id, $fullSections, 0, false);
        }

        return $out;
    }

    /**
     * Получение подразделов в виде дерева.
     *
     * @param int $id Идентификатор раздела корня дерева
     * @param bool|int|string $policy Политика доступа
     *
     * @return array
     */
    public static function getSectionTree($id, $policy = false)
    {
        $fullSections = Policy::getAvailableSections($policy);

        return self::collect($id, $fullSections, 0);
    }

    /**
     * Получение ветки дерева разделов для публичной части.
     *
     * @param int $root Корень ветки
     * @param int $current Текущий (выделенный) раздел
     * @param int $showLvl
     *
     * @return array|bool
     */
    public static function getUserSectionTree($root, $current = 0, $showLvl = 0)
    {
        $fullSections = Policy::getAvailableSections(self::policyUser, $current, true, Visible::$aShowInMenu);

        $parents = [$current];
        if (isset($fullSections['#'][$current])) {
            while ($current = $fullSections['#'][$current]) {
                if (!isset($fullSections['#'][$current])) {
                    break;
                }
                $parents[] = $current;
            }
        }

        $tree = self::collectMarkTree($root, $fullSections, 0, $parents, $showLvl);

        return $tree;
    }

    /**
     * Сбрасывает текущий кэш.
     */
    public static function dropCache()
    {
        self::$AvailableSectionCache = [];
    }

    public static function collect($section, &$list, $lvl = 0, $bTree = true)
    {
        if ($lvl > 10 || !isset($list[$section])) {
            return [];
        }

        $out = [];

        foreach ($list[$section] as $data) {
            if (!isset($list[$data['id']])) {
                if (!$bTree) {
                    $data['title'] = str_repeat('-', $lvl + 1) . $data['title'];
                } else {
                    $data['children'] = [];
                }

                $out[] = $data;
            } elseif ($bTree) {
                $data['children'] = self::collect($data['id'], $list, $lvl + 1, $bTree);
                $out[] = $data;
            } else {
                $data['title'] = str_repeat('-', $lvl + 1) . $data['title'];
                $out[] = $data;
                $out = array_merge($out, self::collect($data['id'], $list, $lvl + 1, $bTree));
            }
        }

        return $out;
    }

    protected static function collectMarkTree($section, &$list, $lvl = 0, $selected = [], $showLvl = 0)
    {
        if ($lvl > 5 || !isset($list[$section])) {
            return false;
        }

        $out = [];

        foreach ($list[$section] as $data) {
            $data['href'] = $data['link'] ?: '[' . $data['id'] . ']';
            $data['items'] = [];

            if (in_array($data['id'], $selected)) {
                $data['selected'] = true;
            }

            if (!isset($list[$data['id']])) {
                $out[] = $data;
            } else {
                if ($showLvl - 1 > $lvl || !$showLvl || isset($data['selected'])) {
                    $data['items'] = self::collectMarkTree($data['id'], $list, $lvl + 1, $selected, $showLvl);
                }

                $out[] = $data;
            }
        }

        return $out;
    }

    /**
     * Добавление нового раздела.
     *
     * @param $iParent
     * @param $sTitle
     * @param int $iTemplateId
     * @param string $sAlias
     * @param int $visible
     * @param string $sLink
     *
     * @return bool|TreeSection
     */
    public static function addSection($iParent, $sTitle, $iTemplateId = 0, $sAlias = '', $visible = Visible::VISIBLE, $sLink = '')
    {
        $section = new TreeSection();
        $section->parent = $iParent;
        $section->alias = $sAlias;
        $section->title = $sTitle;
        $section->visible = $visible;
        $section->link = $sLink;

        if (!$section->save()) {
            return false;
        }

        self::$bClearCache = true;

        if (!$iTemplateId) {
            return $section;
        }

        $section->setTemplate($iTemplateId);

        return $section;
    }

    /**
     * Копирует раздел в другой.
     *
     * @param TreeSection $oSection
     * @param $iParent
     * @param bool|false $bRec
     * @param array $filter
     *
     * @return array
     */
    public static function copySection(TreeSection $oSection, $iParent, $bRec = false, $filter = [])
    {
        self::$copyes = [];

        self::copy($oSection, $iParent, $bRec, $filter);

        return self::$copyes;
    }

    /**
     * Копирует раздел в другой.
     *
     * @param TreeSection $oSection
     * @param $iParent
     * @param bool $bRec
     * @param array $filter
     *
     * @return bool
     * Добавить проверки на зацикливание
     */
    public static function copy(TreeSection $oSection, $iParent, $bRec = false, $filter = [])
    {
        $sTitle = $oSection->title;

        $aServicesParam = \Yii::$app->sections->getByValue($oSection->id);
        if ($aServicesParam) {
            $sParam = $aServicesParam[0];
            $sTitle = \Yii::t('data/app', 'section_' . $sParam, [], Parameters::getLanguage($iParent));
            if ($sTitle == 'section_' . $sParam) {
                $sTitle = $oSection->title;
            }
        }

        $oNewSection = self::addSection(
            $iParent,
            $sTitle,
            $oSection->getTemplate(),
            $oSection->alias,
            $oSection->visible,
            ''
        );

        if (!$oNewSection) {
            return false;
        }

        /* Запомним - пригодится */
        self::$copyes[$oSection->id] = $oNewSection->id;

        /**
         * Если копируется раздел, прописанный как сервисный раздел для чего-либо,
         * то нужно сделать новый такой же параметр для языка создаваемого раздела!
         */
        $lang = Parameters::getLanguage($oNewSection->id);

        if ($aServicesParam) {
            self::$bClearCache = true;
            if ($lang) {
                foreach ($aServicesParam as $sParam) {
                    $sTitle = \Yii::t('data/app', 'section_' . $sParam, [], Parameters::getLanguage($iParent));
                    if ($sTitle == 'section_' . $sParam) {
                        $sTitle = $oSection->title;
                    }
                    \Yii::$app->sections->setSection($sParam, $sTitle, $oNewSection->id, $lang);

                    /* хак на главные разделы*/
                    if ($sParam == 'main') {
                        $oNewSection->save();
                    }
                }
            }
        }

        /**
         * Копирование параметров.
         */
        $aAddParams = Parameters::getList($oSection->id)
            ->level(params\ListSelector::alAll)
            ->get();

        // установить все параметры
        foreach ($aAddParams as $oParam) {
            /* Меняем подписи для диз режима */
            if ($oParam->group == \skewer\build\Design\Zones\Api::layoutGroupName && $oParam->name == \skewer\build\Design\Zones\Api::layoutTitleName) {
                $oParam->value = $oNewSection->title . ' (' . $lang . ')';
            }

            Parameters::setParams(
                $oNewSection->id,
                $oParam->group,
                $oParam->name,
                $oParam->value,
                $oParam->show_val,
                $oParam->title,
                $oParam->access_level
            );
        }

        if ($bRec) {
            $aSubSections = self::getSubSections($oSection->id);
            if ($aSubSections) {
                foreach ($aSubSections as $oSubSection) {
                    if (!$filter || array_search($oSubSection->id, $filter) !== false) {
                        self::copy($oSubSection, $oNewSection->id, $bRec);
                    }
                }
            }
        }

        /* Копирование SEO-параметров */
        $aDataSearch = seo\Api::get(Main\Seo::getGroup(), $oSection->id, 0, true);
        if ($aDataSearch) {
            unset($aDataSearch['id']);
            $aDataSearch['row_id'] = $oNewSection->id;
            seo\Api::set(Main\Seo::getGroup(), 0, 0, $aDataSearch);
        }

        /**
         * Обновление поиска должно быть после добавления параметров в раздел.
         */
        $search = new \skewer\build\Adm\Tree\Search();
        $search->updateByObjectId($oNewSection->id);

        return true;
    }

    /**
     * Удаляет ветву дерева разделов.
     *
     * @param int|string|TreeSection $id
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function removeSection($id)
    {
        $oSection = is_object($id) ? $id : self::getSection($id);

        if (!$oSection) {
            return false;
        }

        return (bool) $oSection->delete();
    }

    /**
     * Вернет список разделов(от главной до текущей).
     *
     * @param int $iSectionId - текущий раздел
     * @param bool $bIncludeCurrentSection - включать текущую
     *
     * @return array
     */
    private static function getChainSections($iSectionId, $bIncludeCurrentSection = true)
    {
        $aData = [];

        $stopSections = Parameters::getValByName($iSectionId, 'pathLine', 'stopSections', true);
        $stopSections = $stopSections ? explode(',', $stopSections) : [];

        $stopSections[] = \Yii::$app->sections->topMenu();
        $stopSections[] = \Yii::$app->sections->leftMenu();
        $stopSections[] = \Yii::$app->sections->serviceMenu();
        $stopSections[] = \Yii::$app->sections->tools();

        $cur = $iSectionId;
        $aVisibleSections = Tree::getVisibleSections(true);
        while ($cur = Tree::getSectionParent($cur, true)) {
            // Дальше стоп - разделов не разбираем
            if (in_array($iSectionId, $stopSections)) {
                break;
            }

            // Пропускаем исключенные из индексирования и из пути разделы
            if (!in_array($cur, $aVisibleSections)) {
                continue;
            }

            $sSectionTitle = Tree::getSectionTitle($cur, true);
            $aData[] = $sSectionTitle;
        }

        if ($bIncludeCurrentSection) {
            array_unshift($aData, Tree::getSectionTitle($iSectionId, true));
        }

        return $aData;
    }

    /**
     * Вернет список разделов до текущей страницы.
     *
     * @param $iSectionId - id текущей страницы
     * @param bool $bIncludeCurrentSection - включать текущую страницу
     * @param string $sDelimiter - Разделитель
     * @param mixed $bForMain
     *
     * @return string
     */
    public static function getChainSectionsToCurrentPage($iSectionId, $bIncludeCurrentSection = true, $sDelimiter = '/', $bForMain = false)
    {
        $sOut = '';

        $aLabels = self::getChainSections($iSectionId, $bIncludeCurrentSection);

        foreach ($aLabels as $sSectionTitle) {
            if ($bForMain) {
                $sOut .= $sSectionTitle . $sDelimiter;
            } else {
                $sOut = $sSectionTitle . $sDelimiter . $sOut;
            }
        }

        $sOut = trim($sOut, $sDelimiter);

        return $sOut;
    }

    /**
     * Проверяет видимость раздела по id
     * @param int $iSectionId
     * @return bool
     */
    public static function isSectionVisible(int $iSectionId): bool
    {
        return in_array($iSectionId, self::getVisibleSections());
    }
}
