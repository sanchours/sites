<?php

namespace skewer\components\search;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\ui\ARSaveException;
use skewer\components\search\models\SearchIndex;
use skewer\components\seo;
use skewer\components\seo\Service;
use yii\helpers\HtmlPurifier;

/**
 * Класс прототип для поисковых механизмов контентных сущностей.
 */
abstract class Prototype
{
    /** @var SearchIndex AR поисковой записи */
    protected $oSearchIndexRow;

    /** @var TreeSection Раздел, в котором выводится сущность */
    protected $oSection;

    /** @var mixed Сущность(товар, новость и т.д.) */
    protected $oEntity;

    /**
     * @var string псевдоним поискового движка,
     *      который передается основным обработчиком при итерации
     *      поисковых записей.
     *      В большинстве случаев совпадает с ответом метода getName()
     */
    protected $sIncomingName = '';

    /**
     * отдает имя идентификатора ресурса для работы с поисковым индексом
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Отдает название модуля.
     *
     * @return string
     */
    public function getModuleTitle()
    {
        if (!\Yii::$app->register->moduleExists($this->getName(), Layer::PAGE)) {
            return '-';
        }

        return \Yii::$app->register->getModuleConfig($this->getName(), Layer::PAGE)->getTitle();
    }

    /**
     * Нужно ли создавать запись об этой сущности в таблицу search_index.
     *
     * @param int $iObjectId - ид сущности
     *
     * @return bool
     */
    protected function doSupportSearchIndex($iObjectId)
    {
        return true;
    }

    /**
     * Проверяет конкретную запись поисковой таблицы.
     *
     * @return bool - вернет false, если запись должна быть исключена из поиска и sitemap
     *              - вернет true, если запись может учавствовать в поиске и sitemap
     */
    protected function checkRow()
    {
        // Здесь могут быть выполнены действия по сбросу других записей
        $this->beforeUpdate();

        // Проверить сущность
        if (!$this->checkEntity()) {
            return false;
        }

        // Запрашиваем sectionId, т.к. он мог поменяться
        $iNewSection = $this->getNewSectionId();

        if ($iNewSection === false) {
            return false;
        }

        $this->oSearchIndexRow->section_id = $iNewSection;

        // Взять раздел
        $this->oSection = $this->grabSection();

        // Проверить раздел
        if (!$this->checkSection()) {
            return false;
        }

        return true;
    }

    /**
     *  воссоздает полный список пустых записей для сущности, отдает количество добавленных.
     *
     * @return int
     */
    abstract public function restore();

    /**
     * сбрасывает статус для всех записей с идентификатором модуля, отдает количество измененных.
     *
     * @return int
     */
    public function resetAll()
    {
        return SearchIndex::updateAll(['status' => 0], ['class_name' => $this->getName(), 'status' => 1]);
    }

    /**
     * сбрасывает по id объекта в 0.
     *
     * @param int $id
     *
     * @return int
     */
    public function resetToId($id)
    {
        return SearchIndex::updateAll(['status' => 0], ['class_name' => $this->getName(), 'object_id' => $id]);
    }

    /**
     * Сбросить статус в 0 по section_id.
     *
     * @param int $iSectionId - ид раздела
     *
     * @return int  количество обновленных записей
     */
    public function resetBySectionId($iSectionId)
    {
        return SearchIndex::updateAll(['status' => 0], ['class_name' => $this->getName(), 'section_id' => $iSectionId]);
    }

    /**
     * Удалить запись по object_id.
     *
     * @param int - object_id записи
     * @param mixed $iId
     *
     * @return int кол-во удалённых записей
     */
    public function deleteToObjectId($iId)
    {
        return SearchIndex::deleteAll(['class_name' => $this->getName(), 'object_id' => $iId]);
    }

    /**
     * удаляет все записи из поискового индекса, отдает количество измененных.
     *
     * @return int
     */
    public function deleteAll()
    {
        $res = SearchIndex::deleteAll(['class_name' => $this->getName()]);
        Service::updateSiteMap();

        return $res;
    }

    /**
     * отдает число неактивных записей.
     *
     * @return int
     */
    public function getInactiveCount()
    {
        return SearchIndex::find()->where(['class_name' => $this->getName(), 'status' => 0])->count();
    }

    /**
     * обновляет/добавляет запись в поисковой таблице по id конкретной записи ресурса
     * выбирает / создает если нет поисковую запись по id и идентификатору ресурса.
     *
     * @param $iId
     * @param bool $bAddSitemapTask нужно ли ставить задачу на обновление sitemap?
     *
     * @throws ARSaveException
     *
     * @return bool
     */
    public function updateByObjectId($iId, $bAddSitemapTask = true)
    {
        // Нужно ли создавать запись об этой сущности в таблицу search_index
        if (!$this->doSupportSearchIndex($iId)) {
            $this->oSearchIndexRow = $this->getExistOrNewRecord($iId);
            $this->updateAsEmpty(true);
            return true;
        }

        // выбираем или создаём(если нет) запись
        $this->oSearchIndexRow = $this->getExistOrNewRecord($iId);

        // не указан object_id - удалить
        if (!$this->oSearchIndexRow->object_id) {
            $this->deleteToObjectId($this->oSearchIndexRow->object_id);

            return true;
        }

        $this->oEntity = $this->grabEntity();

        // нет записи сущности - удалить
        if (!$this->oEntity) {
            $this->deleteToObjectId($this->oSearchIndexRow->object_id);

            return true;
        }

        // Проверяем можно ли использовать запись в поиске и sitemap
        $res = $this->checkRow();

        $this->oSearchIndexRow->has_real_url = $this->buildHasRealUrl();
        $this->oSearchIndexRow->href = $this->buildHrefSearchIndexRow();

        if ($res === false) {
            $this->oSearchIndexRow->use_in_search = false;  // не использовать в поиске
            $this->oSearchIndexRow->use_in_sitemap = false;  // не использовать в sitemap
        } else {
            $this->oSearchIndexRow->use_in_search = true;  // использовать в поиске
            $this->oSearchIndexRow->use_in_sitemap = true;  // использовать в sitemap
            $this->fillSearchRow();                         // Заполнить поисковой AR данными
        }

        $this->oSearchIndexRow->status = 1; // обработана
        $this->oSearchIndexRow->save();

        if ($this->oSearchIndexRow->hasErrors()) {
            throw new ARSaveException($this->oSearchIndexRow);
        }

        // если стоит флаг - поставить задачу на обновление sitemap
        if ($bAddSitemapTask) {
            seo\Api::setUpdateSitemapFlag();
        }

        return $res;
    }

    /**
     * Найдёт запись по object_id, если такой нет - создаст новую.
     *
     * @param int $iId - object_id записи
     *
     * @return SearchIndex
     */
    protected function getExistOrNewRecord($iId)
    {
        // найти запись
        $oSearchRow = SearchIndex::find()
            ->where(['class_name' => $this->getName(), 'object_id' => $iId])
            ->one();

        // ... или создать новую
        if (!$oSearchRow) {
            $oSearchRow = new SearchIndex();
            $oSearchRow->class_name = $this->getName();
            $oSearchRow->object_id = (int) $iId;
            $oSearchRow->save();
        }

        $oSearchRow->class_name = $this->getName();

        return $oSearchRow;
    }

    /**
     * удаляет запись по id и идентификатору ресурса.
     *
     * @param $iId
     *
     * @return bool
     */
    public function deleteByObjectId($iId)
    {
        $res = (bool) SearchIndex::deleteAll(['class_name' => $this->getName(), 'object_id' => $iId]);
        Service::updateSearchIndex();
        Service::updateSiteMap();

        return $res;
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

    /**
     * Задает имя под которым поисковая записб зарегистрирована в таблице.
     *
     * @param string $sName
     */
    public function provideName($sName)
    {
        $this->sIncomingName = $sName;
    }

    /**
     * Расширенная функция удаления тегов
     * Умеет стирать также теги типа style, script.
     *
     * @param $sText
     *
     * @return string
     */
    protected function stripTags($sText)
    {
        return strip_tags(HtmlPurifier::process($sText));
    }

    /**
     * Заполняет объект поисковой записи seo данными.
     *
     * @param SearchIndex $oSearchRow - поисковая запись
     * @param seo\SeoPrototype $oSeo - seo -компонент
     */
    protected function fillSearchRowSeoData(SearchIndex &$oSearchRow, seo\SeoPrototype $oSeo)
    {
        // загружаем seo данные в компонент
        $oSeo->initSeoData();

        if ($oSeo->none_index) {
            $oSearchRow->use_in_sitemap = false;
            $oSearchRow->use_in_search = false;
        }

        if ($oSeo->none_search) {
            $oSearchRow->use_in_search = false;
        }

        $oSearchRow->priority = ($oSeo->priority)
            ? $oSeo->priority
            : $oSeo->calculatePriority();

        $oSearchRow->frequency = ($oSeo->frequency)
            ? $oSeo->frequency
            : $oSeo->calculateFrequency();
    }

    /**
     * Метод получения сущности(товар, новость, элемент коллекции и т.д.).
     *
     * @return mixed
     */
    abstract protected function grabEntity();

    /**
     * Проверка соответствия сущности условиям попадания в поисковой индекс
     *
     * @return bool
     */
    abstract protected function checkEntity();

    /**
     * Метод заполнения $this->oSearchIndexRow данными.
     */
    abstract protected function fillSearchRow();

    /**
     * Метод получения раздела.
     *
     * @return TreeSection
     */
    protected function grabSection()
    {
        return Tree::getSection($this->oSearchIndexRow->section_id);
    }

    /**
     * Проверка соответствия раздела условиям попадания в поисковой индекс
     *
     * @return bool
     */
    protected function checkSection()
    {
        // проверка существования раздела и реального url у него
        if (!$this->oSection || !$this->oSection->hasRealUrl()) {
            return false;
        }

        return true;
    }

    /**
     * Получить новый section_id
     * Для тех сущностей, которые могут поменять раздел вывода, ид раздела следует брать от сущности
     * Для остальных от $this->oSearchIndexRow->section_id.
     *
     * @return int
     */
    protected function getNewSectionId()
    {
        return $this->oSearchIndexRow->section_id;
    }

    /**
     * Действия до обновления поисковой записи
     * Здесь можно написать инструкции выполняющие дополнительные действия с поисковыми записями
     * Например: сброс статуса поис.записей подчиненным сущностям
     */
    protected function beforeUpdate()
    {
    }

    /**
     * Построит ссылку на поисковую запись.
     *
     * @return string
     */
    abstract protected function buildHrefSearchIndexRow();

    /**
     * Отдаст значение флага "имеет реальный урл".
     *
     * @return bool
     */
    protected function buildHasRealUrl()
    {
        return true;
    }

    /**
     * Сохранение записи со сбросом значащих полей.
     *
     * @param bool $bHandled - запись обработана?
     *
     * @return bool
     */
    public function updateAsEmpty($bHandled)
    {
        // Сбрасываем значащие поля
        $this->oSearchIndexRow->search_title = '';
        $this->oSearchIndexRow->search_text = '';
        $this->oSearchIndexRow->text = '';

        $this->oSearchIndexRow->status = $bHandled ? 1 : 0;

        return $this->oSearchIndexRow->save();
    }
}
