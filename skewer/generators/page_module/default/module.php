<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_module\Generator
 */
use skewer\generators\page_module\Api;

$className = $generator->moduleName;
    $nameDict = $generator->nameDict;
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Page\\' . $className;
    echo "<?php\n";
?>

namespace <?= $ns; ?>;
<?php foreach (Api::getUses($nameDict) as $sUse): ?>
use <?= $sUse; ?>;
<?endforeach; ?>
use skewer\components\traits\CanonicalOnPageTrait;
use skewer\base\orm\ActiveRecord;

/**
 *  Class Module
 * @package skewer\build\Page\<?= $className . "\n"; ?>
 */
class Module extends site_module\page\ModulePrototype {
    use CanonicalOnPageTrait;

    public $listTemplate = 'list.php';
    public $detailTemplate = 'detail_page.php';

    public $nameDict = "<?= $nameDict; ?>";
    public $onPage = 10;

<?php foreach (Api::getArrayPrototypeView($nameDict) as $oField) {
    if ($properties = $oField->getProperties()) {
        foreach ($properties as $property) {
            echo "    {$property}\n";
        }
    }
}
?>

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
        $aListElement = Query::SelectFrom($sNameTable)
                                ->setCounterRef($iCount)
                                ->limit($this->onPage,($page-1)*$this->onPage)
                                ->order('priority')
                                ->getAll();

        foreach ($aListElement as &$element) {

            $hrefParam = isset($element['alias']) && $element['alias']
                ? "dict_alias={$element['alias']}"
                : "dict_id={$element['id']}";

            $element['href'] = "[{$this->sectionId()}}][<?=$className; ?>?" . $hrefParam . "]";
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
        $oDict = Dict::getValByString($this->nameDict, $alias, false, 'alias');
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

<?php foreach (Api::getArrayPrototypeView($nameDict) as $oField) {
    $sCode = $oField->getCodeDetail();
    if ($sCode) {
        echo "        {$sCode}\n\n";
    }
}
?>
        foreach ($aFieldDict as $sName=>$sValue) {
            $this->setData($sName,$sValue);
        }
        
        $this->setData('aNameField', $aNameField);
        $this->setData('aFieldDict', $aFieldDict);
        $this->setTemplate($this->detailTemplate);
        return psComplete;

    }

}