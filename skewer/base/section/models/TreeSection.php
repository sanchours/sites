<?php

namespace skewer\base\section\models;

use skewer\base\section;
use skewer\base\section\Tree;
use skewer\build\Adm\CategoryViewer\models\CategoryViewerCssParams;
use skewer\build\Adm\Tree\Exporter;
use skewer\build\Adm\Tree\Importer;
use skewer\build\Adm\Tree\Search;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Page\Main;
use skewer\build\Tool\SeoGen\exporter\GetListExportersEvent;
use skewer\build\Tool\SeoGen\importer\GetListImportersEvent;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\Policy;
use skewer\components\seo;
use skewer\helpers\Transliterate;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\base\UserException;
use yii\db\AfterSaveEvent;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "tree_section".
 *
 * @property int $id
 * @property string $alias
 * @property string $title
 * @property int $parent
 * @property int $visible
 * @property int $type
 * @property int $position
 * @property string $alias_path
 * @property string $link
 * @property int $level
 * @property string $last_modified_date
 *
 * @method static TreeSection findOne($condition)
 * @method static TreeSection[] findAll($condition)
 */
class TreeSection extends \skewer\components\ActiveRecord\ActiveRecord
{
    const EVENT_AFTER_CREATE = 'EVENT_AFTER_CREATE';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tree_section';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['alias', 'title', 'parent', 'visible', 'type', 'position', 'alias_path', 'link', 'level'], 'required'],
            [['parent', 'visible', 'type', 'position', 'level'], 'integer'],
            [['last_modified_date'], 'safe'],
            [['alias', 'title', 'alias_path', 'link'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alias' => 'Alias',
            'title' => 'Title',
            'parent' => 'Parent',
            'visible' => 'Visible',
            'type' => 'Type',
            'position' => 'Position',
            'alias_path' => 'Alias Path',
            'link' => 'Link',
            'level' => 'Level',
            'last_modified_date' => 'Last Modified Date',
        ];
    }

    /**
     * Установка шаблонного раздела с копированием параметров, если нужно.
     *
     * @param int $tpl Id шаблонного раздела
     * @param bool $bCopyParams Копировать параметры шаблона?
     *
     * @return bool
     */
    public function setTemplate($tpl, $bCopyParams = true)
    {
        // id шаблона
        if (!Tree::getSection($tpl)) {
            return false;
        }

        section\Parameters::setParams($this->id, section\Parameters::settings, section\Parameters::template, $tpl);

        if ($bCopyParams) {
            // запросить данные
            $aAddParams = section\Parameters::getList($tpl)
                ->level(\skewer\base\section\params\ListSelector::alPos)
                ->fields(['id', 'title', 'value', 'show_val', 'access_level'])
                ->get();

            // добавить все редактируемые параметры, кроме языковых
            foreach ($aAddParams as $oParam) {
                if ($oParam->access_level != section\params\Type::paramLanguage) {
                    section\Parameters::copyToSection($oParam, $this->id);
                }
            }
        }

        /* Нужно выкинуть сообщение о том, что раздел создан уже после копирования в него параметров из его шаблона */
        Event::trigger(self::className(), self::EVENT_AFTER_CREATE, new AfterSaveEvent([
            'sender' => $this,
            'changedAttributes' => $this->getAttributes(),
        ]));

        return true;
    }

    public function getTemplate()
    {
        return section\Parameters::getTpl($this->id);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        // флаг того, что страница - главная на сайте
        $bMain = in_array($this->id, \Yii::$app->sections->getValues('main'));

        $this->title = mb_substr($this->title, 0, 100);
        $this->last_modified_date = date('Y-m-d H:i:s', time());

        if (!$this->alias) {
            $this->alias = $this->title ?: 'section';
        }
        $this->alias = Transliterate::generateAlias($this->alias);
        $this->alias = mb_substr($this->alias, 0, 60);

        if ($this->isAttributeChanged('title')
            || $this->isAttributeChanged('alias')
            || $this->isAttributeChanged('parent')
            || $this->isAttributeChanged('visible')) {
            $this->alias = seo\Service::generateAlias($this->alias, $this->id, $this->parent, 'Page');
        }

        $this->checkPosition();
        $oParentSection = $this->getParentSection();
        $this->checkLevel($oParentSection);

        // походу хак для корневых разделов, но главную выводим
        if (!$this->parent and !$bMain) {
            $this->visible = -1;
        }

        /**
         * @var bool флаг необходимости перестроения пути
         * вызывается до сохранения, иначе потом факт изменения затрется
         */
        $bRebuildPath = $this->isAttributeChanged('alias') ||
            $this->isAttributeChanged('parent') ||
            $this->isAttributeChanged('visible');

        // изменить alias_path и, если нужно alias
        if ($oParentSection) {
            $basePath = $oParentSection->alias_path ?: '';
            $this->alias_path = $this->genAliasPath($basePath);
        }

        $res = parent::save($runValidation, $attributeNames);

        Policy::incPolicyVersion();
        CurrentAdmin::reloadPolicy();

        // рекурсивное обновление alias_path у дочерних разделов, если изменены некоторые поля
        if ($bRebuildPath) {
            $this->changeAliasPath($this->alias_path);
        }

        return $res;
    }

    /**
     * Получить родитеский раздел.
     *
     * @return TreeSection | null
     */
    protected function getParentSection()
    {
        return $this->parent ? self::findOne(['id' => $this->parent]) : null;
    }

    /**
     * Проверка и генерация веса для сортировки.
     */
    protected function checkPosition()
    {
        // если родительского раздела нет (ни к чему не привязан),
        //      то не перестраиваем позицию
        if (!$this->parent) {
            return true;
        }

        // Если родительский раздел не изменился, то пропускаем
        // Такое может быть при создании нового и при переносе в другой раздел
        if (!$this->isAttributeChanged('parent')) {
            return true;
        }

        // если позиция была задана принудительно - пропускаем
        if ($this->isAttributeChanged('position')) {
            return true;
        }

        /** @var self $section */
        $section = self::find()
            ->where(['parent' => $this->parent])
            ->orderBy(['position' => SORT_DESC])
            ->one();

        $this->position = $section ? $section->position + 1 : 1;

        return true;
    }

    /**
     * Генератор уникального поля alias_path и alias.
     *
     * @param string $basePath Значение alias_path родителя
     *
     * @return string
     */
    protected function genAliasPath($basePath)
    {
        if ($this->visible == section\Visible::HIDDEN_FROM_PATH) {
            $sPath = $basePath;

        // флаг того, что страница - главная на сайте
        } elseif ($bMain = in_array($this->id, \Yii::$app->sections->getValues('main'))) {
            // главная должна быть с корневым url

            $oLangRootSection = self::findOne(['id' => \Yii::$app->sections->getValue(section\Page::LANG_ROOT, section\Parameters::getLanguage($this->parent))]);

            if ($oLangRootSection and $oLangRootSection->alias_path) {
                $sPath = $oLangRootSection->alias_path;
            } else {
                $sPath = '/';
            }
        } else {
            $i = '';
            $sAliasBase = $this->alias ?: $this->id ?: 'section';
            do {
                $this->alias = $sAliasBase . $i;
                $sPath = $basePath . $this->alias . '/';

                $res = self::find()
                    ->andWhere(['alias_path' => $sPath])
                    ->andWhere(['<>', 'id', (int) $this->id])
                    ->andWhere(['<>', 'visible', section\Visible::HIDDEN_FROM_PATH])
                    ->andWhere(['not', ['id' => \Yii::$app->sections->getValues('main')]])
                    ->one();

                ++$i;
            } while ($res);
        }

        return $sPath;
    }

    /**
     * Рекурсивное обновление alias_path для дочерних разделов.
     *
     * @param string $basePath
     */
    protected function changeAliasPath($basePath = '/')
    {
        foreach (self::findAll(['parent' => $this->id]) as $section) {
            $path = $section->genAliasPath($basePath);
            self::updateAll(['alias_path' => $path], ['id' => $section->id]);
            $section->changeAliasPath($path);
        }
    }

    /**
     * Перенос ветки разделов по дереву.
     *
     * @param TreeSection $section Раздел в который переносится ветка
     * @param string $direction Тип переноса append|before|after
     *
     * @throws UserException
     *
     * @return bool
     */
    public function changePosition(TreeSection $section, $direction = 'append')
    {
        // направление переноса
        switch ($direction) {
            // добавить как подчиненный
            case 'append':

                // изменить положение и родителя раздела
                $this->parent = $section->id;
                $this->save();

                break;

            // вставить до/после элемента
            case 'before':
            case 'after':

                // в корень писать нельзя, из него забирать нельзя, но сортировать можно
                if ($this->parent xor $section->parent) {
                    throw new UserException('badData');
                }
                $position = ($direction == 'before') ? $section->position : $section->position + 1;

                $query = self::find()
                    ->where(
                        '`parent` = :parent AND `position` >= :position',
                        [':parent' => $section->parent, ':position' => $position]
                    );

                foreach ($query->each() as $curSection) {
                    $curSection->updateCounters(['position' => 1]);
                }

                $this->parent = $section->parent;
                $this->position = $position;

                $this->save();

                $this->changeAliasPath($this->alias_path);

                break;

            // неподдерживаемый вариант
            default:
                throw new UserException('badData');
        }

        return true;
    }

    /**
     * Возможность доступа текущего админа к разделу.
     *
     * @return bool
     */
    public function testAdminAccess()
    {
        return CurrentAdmin::canRead($this->id);
    }

    /**
     * Набор подразделов.
     *
     * @return TreeSection[]
     */
    public function getSubSections()
    {
        return self::findAll(['parent' => $this->id]);
    }

    private function checkLevel(TreeSection $oParentSection = null)
    {
        if ($this->level) {
            return true;
        }

        $this->level = $oParentSection ? $oParentSection->level + 1 : 0;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            /*Удаление параметров удаляемого раздела*/
            ParamsAr::deleteAll([
                'parent' => $this->id,
            ]);

            /*Удалить css-параметры разводки */
            CategoryViewerCssParams::deleteForSections($this->id);

            // сначала рекурсивно удаляем подчиненные разделы
            foreach ($this->getSubSections() as $oSubSection) {
                $oSubSection->delete();
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        section\ParamCache::clear();

        section\Template::clearTemplateCache();

        $search = new Search();

        // если поменялись нужные поля - рекурсивно перестроить индекс по дереву
        if (array_intersect(['visible', 'alias', 'link', 'alias_path'], array_keys($changedAttributes))) {
            $search->setRecursiveResetFlag();
        }

        $search->updateByObjectId($this->id);
        \Yii::$app->router->updateModificationDateSite();
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        section\ParamCache::clear();

        section\Template::clearTemplateCache();

        /* Удаление SEO-параметров */
        seo\Api::del(Main\Seo::getGroup(), $this->id);

        // Удаление seo шаблона
        if ($oSeoTpl = seo\Template::getByAliases(SeoGood::getAlias(), $this->id)) {
            $oSeoTpl->delete();
        }

        \Yii::$app->router->updateModificationDateSite();
    }

    /**
     * Метод, вызываемый при удалении раздела.
     *
     * @param ModelEvent $event
     */
    public static function onSectionDelete(ModelEvent $event)
    {
        FileHelper::removeDirectory(FILEPATH . $event->sender->id);

        $search = new Search();
        $search->setRecursiveResetFlag();
        $search->deleteByObjectId($event->sender->id);
    }

    /**
     * Класс для сборки списка автивных поисковых движков.
     *
     * @param \skewer\components\search\GetEngineEvent $event
     */
    public static function getSearchEngine(\skewer\components\search\GetEngineEvent $event)
    {
        $event->addSearchEngine(Search::className());
    }

    /**
     * Отдает флаг наличия собственного url у раздела
     *  по которому он может быть открыт
     *
     * @return bool
     */
    public function hasRealUrl()
    {
        if ($this->link) {
            return false;
        }

        return in_array(
            $this->visible,
            section\Visible::$aOpenByLink
        );
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return (new \yii\db\Query())->select('MAX(`last_modified_date`) as max')->from(self::tableName())->one();
    }

    /**
     * Обновляет дату модификации раздела.
     *
     * @param int $iSectionId - id раздела
     * @param int $iTimestamp - дата модификации
     */
    public static function updateLastModify($iSectionId, $iTimestamp = null)
    {
        if (!$iTimestamp) {
            $iTimestamp = time();
        }

        TreeSection::updateAll(['last_modified_date' => date('Y-m-d H:i:s', $iTimestamp)], ['id' => $iSectionId]);
    }

    /**
     * Регистрирует класс Importer, в списке импортёров события $oEvent.
     *
     * @param GetListImportersEvent $oEvent
     */
    public static function getImporter(GetListImportersEvent $oEvent)
    {
        $oEvent->addImporter(Importer::className());
    }

    /**
     * Регистрирует класс Exporter, в списке экпортёров события $oEvent.
     *
     * @param GetListExportersEvent $oEvent
     */
    public static function getExporter(GetListExportersEvent $oEvent)
    {
        $oEvent->addExporter(Exporter::className());
    }
}
