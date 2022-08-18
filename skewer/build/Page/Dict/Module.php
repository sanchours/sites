<?php

namespace skewer\build\Page\Dict;

use skewer\base\orm\Query;
use skewer\base\site_module;
use skewer\components\catalog\Dict;
use skewer\base\site\Page;
use skewer\components\traits\CanonicalOnPageTrait;
use skewer\base\orm\ActiveRecord;
use skewer\base\section\Parameters;
use skewer\components\gallery\Album;
use skewer\components\gallery\Photo;

/**
 *  Class Module
 * @package skewer\build\Page\Dict
 */
class Module extends site_module\page\ModulePrototype implements site_module\Ajax {
    use CanonicalOnPageTrait;

    public $listTemplate = 'list.php';
    public $detailTemplate = 'detail_page.php';
    public $nameDict = 'default';
    public $onPage = 10;
    public $section = 0;
    public $pictureField = "picture";
    public $galleryListFormat = "img_preview";

    public $moduleName = 'Tool';

    public function sectionId()
    {
        return $this->section ?? parent::sectionId();
    }

    public function init()
    {
        $this->setParser(parserPHP);
        return true;
    }

    /**
     * Выводит список элементов из справочника
     * @param int $page номер страницы
     * @return int
     * @throws \yii\base\UserException
     */
    public function actionIndex($page = 1)
    {
        if (!$this->onPage) {
            return psComplete;
        }

        $iCount = 0;
        
        $sNameTable = Dict::getDictTableName($this->nameDict);

        if (empty(Dict::getDictIdByName($this->nameDict, $this->moduleName))) {
            return psComplete;
        }

        $aListElement = Query::SelectFrom($sNameTable)
                                ->setCounterRef($iCount)
                                ->limit($this->onPage,($page-1)*$this->onPage)
                                ->order('priority')
                                ->getAll();

        foreach ($aListElement as &$element) {

            $hrefParam = isset($element['alias']) && $element['alias']
                ? "dict_alias={$element['alias']}"
                : "dict_id={$element['id']}";

            $element['href'] = "[{$this->sectionId()}][Dict?" . $hrefParam . "]";

            if (isset($element[$this->pictureField])) {
                $element['first_image'] = Album::getFirstActiveImage($element[$this->pictureField], $this->galleryListFormat);
            }

            $lang = Parameters::getLanguage($this->sectionId());
            if (!empty($lang) && $lang !== 'ru') {
                foreach ($element as $field => $val) {
                    $eKey = $field . '_'.$lang;
                    if (key_exists($eKey, $element) && !empty($element[$eKey])) {
                        $element[$field] = $element[$eKey];
                    }
                }
            }
        }
        $this->setData('aListElement', $aListElement);

        $this->getPageLine($page, $iCount, $this->sectionId(), [], ['onPage' => $this->onPage]);

        $this->setTemplate($this->listTemplate);
        $this->setData('sectionId', $this->sectionId());
        
        return psComplete;
        
    }

    /**
     * Выводит список элементов по id
     * @return int
     * @throws \yii\base\UserException
     */
    public function actionViewById()
    {
        $id = $this->get('dict_id');
        $oDict = Dict::getValues($this->nameDict, $id);

        if ($oDict instanceof ActiveRecord) {
            $this->setCanonicalByAlias(
                $this->sectionId(),
                $oDict->alias
            );
        }
        return $this->showOne($oDict);
    }
    
    /**
     * Выводит список элементов по alias
     * @return int
     * @throws \yii\base\UserException
     */
    public function actionViewByAlias()
    {
        $alias = $this->get('dict_alias');
        if (!empty(Dict::getDictByName($this->nameDict))) {
            $oDict = Dict::getValByString($this->nameDict, $alias, false, 'alias');
        }
        if (empty($oDict)) return false;
        return $this->showOne($oDict);
    }

    /**
     * Выводит элемент
     * @param \skewer\base\orm\ActiveRecord $oDict
     * @return int
     * @throws \yii\base\UserException
     */
    public function showOne($oDict)
    {
        $aNameField = [];
        $aFieldDict = $oDict->getData();
        Page::setTitle(false);

        // добавляем элемент в pathline
        Page::setAddPathItem($aFieldDict['title']);

        foreach ($aFieldDict as $sFieldName => $sValue) {
            $aNameField[$sFieldName] = $oDict->getModel()->getFiled($sFieldName)->getTitle();
        }

        foreach ($aFieldDict as $sName=>$sValue) {
            $this->setData($sName,$sValue);
        }

        if (isset($aFieldDict['picture'])) {
            $aFieldDict['gallery'] = Photo::getFromAlbum($aFieldDict['picture']);
        }
        
        $this->setData('aNameField', $aNameField);
        $this->setData('aFieldDict', $aFieldDict);
        $this->setTemplate($this->detailTemplate);
        $this->setData('sectionId', $this->sectionId());
        return psComplete;

    }

    public function actionGetDataForReact()
    {
        $name = $this->get('name');
        echo json_encode(Dict::getValues($name));
        exit;
    }

}