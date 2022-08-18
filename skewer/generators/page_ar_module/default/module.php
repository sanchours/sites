<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_ar_module\Generator
 */
    $className = $generator->moduleName;
    $nameAR = $generator->nameAR;
    $descAR = $generator->aDescARs[$nameAR];
    $pathAR = $generator->pathAR;
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Page\\' . $className;
    echo "<?php\n";
?>

namespace <?= $ns; ?>;

use <?=$pathAR; ?>;
use skewer\base\site_module\page\ModulePrototype;
use skewer\base\site\Page;
/**
 *  Class Module
 * @package skewer\build\Page\<?= $className . "\n"; ?>
 */
class Module extends ModulePrototype {

    public $listTemplate = 'list.twig';
    public $detailTemplate = 'detail.twig';
<?php if ($generator->aNameARs !== []):
foreach ($generator->aNameARs as $item):
$item = mb_strtolower($item); ?>
    public $<?=$item; ?>ListTemplate = '<?=$item; ?>_list.twig';
    public $<?=$item; ?>DetailTemplate = '<?=$item; ?>_detail.twig';
<?php endforeach; endif; ?>

    public $iOnPage = 10;
    public $iPageNum = 0;
    

    public function init() {

        // номер страницы
        $this->iPageNum = $this->getInt('page');

        $this->setParser(parserTwig);
        return true;
    }


    /**
    * Выводит список элементов из справочника
    * @param int $page номер страницы
    * @return int
    */
    public function actionIndex() {
    
        if (!$this->iOnPage) return psComplete;

        // Получаем список элементов AR
        $query = models\<?=$nameAR; ?>::find()
            ->limit($this->iOnPage)
            ->offset($this->iPageNum * $this->iOnPage);

<?php if (isset($descAR->columns['priority'])): ?>
        $query->orderBy('priority');
<?php endif; ?>
        $aItems = $query->asArray()->all();

        $iCount = models\<?=$nameAR; ?>::find()
            ->count();

        foreach ($aItems as $key=>$item){
<?if (isset($descAR->columns['alias'])):?>
            if(isset($item['alias']) && $item['alias']){
                $url= Api::getUrl($item['alias'], 'alias');
            }else{
<?php endif; ?>
                $url= Api::getUrl($item['id']);
<?if (isset($descAR->columns['alias'])):?>
            }
<?php endif; ?>
            $aItems[$key]['url'] = $url;
        }

        //пагинатор
        $this->getPageLine($this->iPageNum, $iCount, $this->sectionId(), [], array( 'onPage' => $this->iOnPage));
        
        $this->setTemplate($this->listTemplate);
        $this->setData('a<?=$nameAR; ?>',$aItems);
        $this->setData('sectionId',$this->sectionId());
        
        return psComplete;
        
    }

    /**
    * Выводит элемент
    * @param \skewer\base\orm\ActiveRecord $oDict
    * @return int
    */
    public function showOne( $a<?=$nameAR; ?> ) {

        Page::setTitle(false);
        // добавляем элемент в pathline
        Page::setAddPathItem( $a<?=$nameAR; ?>['title'] );

        $this->setData('a<?=$nameAR; ?>',$a<?=$nameAR; ?>);
        $this->setTemplate($this->detailTemplate);
        return psComplete;

    }

    /**
    * Выводит список элементов по id
    */
    public function actionViewById() {
        $id = $this->get('cars_id');
        $a<?=$nameAR; ?> = models\<?=$nameAR; ?>::find()
                            ->where(['id'=>$id])
                            ->asArray()->one();

        return $this->showOne($a<?=$nameAR; ?>);
    }
    
    /**
    * Выводит список элементов по alias
    */
    public function actionViewByAlias() {
        $alias = $this->get('cars_alias');
        $a<?=$nameAR; ?> = models\<?=$nameAR; ?>::find()
                            ->where(['alias'=>$alias])
                            ->asArray()->one();

        return $this->showOne($a<?=$nameAR; ?>);
    }


<?php
// добавление функций для дополнительных моделей
if ($generator->aNameARs !== []):
$languageCategory = mb_strtolower($item);
foreach ($generator->aNameARs as $item):
$descAR = $generator->aDescARs[$item];
$lowerItem = mb_strtolower($item); ?>
    /**
    * Выводит спарсенный шаблон списка элементов из сущности AR <?=$item; ?>

    */
    public function get<?=$item; ?>List($aConditional = []) {

        $query = models\<?=$item; ?>::find();

        if($aConditional){
            $query->where($aConditional);
        }
<?php if (isset($descAR->columns['priority'])): ?>
        $query->orderBy('priority');
<?php endif; ?>
        $aItems = $query->asArray()->all();

        $data = ['<?=mb_strtolower($nameAR); ?>_id'=> $this->get('<?=mb_strtolower($nameAR); ?>_id'),
                 '<?=mb_strtolower($nameAR); ?>_alias'=> $this->get('<?=mb_strtolower($nameAR); ?>_alias')];

        foreach ($aItems as $key=>$item){
            $data['item] = $item;
            $aItems[$key]['url'] = Api::get<?=$item; ?>Url($data);
        }

        return $this->renderTemplate($this-><?=$lowerItem; ?>ListTemplate,
                                    ['a<?=$item; ?>'=>$aItems,
                                     'sectionId'=> $this->sectionId()]);
    }

    /**
    * Отдает массив элементов из сущности AR <?=$item; ?>

    */
    public function get<?=$item; ?>AsArrayList($aConditional = []) {

        $query = models\<?=$item; ?>::find();

        if($aConditional){
            $query->where($aConditional);
        }
<?php if (isset($descAR->columns['priority'])): ?>
        $query->orderBy('priority');
 <?php endif; ?>
        $aItems = $query->asArray()->all();

        return $aItems;
    }

    /**
    * Выводит спарсенный шаблон детальной экземпляра AR <?=$item; ?>

    */
    public function get<?=$item; ?>ShowOne($aConditional = []) {

        $query = models\<?=$item; ?>::find();

        if($aConditional){
            $query->where($aConditional);
        }

        $aItem = $query->asArray()->one();
        return $this->renderTemplate($this-><?=$lowerItem; ?>DetailTemplate,
                                    ['a<?=$item; ?>'=>$aItem,
                                     'sectionId'=> $this->sectionId()]);

    }

    /**
    * Отдает элемент в виде массива из сущности AR <?=$item; ?>

    */
    public function get<?=$item; ?>AsArrayOne($aConditional = []) {

        $query = models\<?=$item; ?>::find();

        if($aConditional){
            $query->where($aConditional);
        }

        $aItem = $query->asArray()->one();
        return $aItem;

    }


    /**
    * Выводит экземпляр AR <?=$item; ?>

    * @return int
    */
    public function <?=$lowerItem; ?>ShowOne( $a<?=$item; ?>) {

        Page::setTitle(false);
        // добавляем элемент в pathline
        Page::setAddPathItem( $a<?=$item; ?>['title'] );

        $this->setData('a<?=$item; ?>',$a<?=$item; ?>);
        $this->setData('sectionId',$this->sectionId());
        $this->setTemplate($this-><?=$lowerItem; ?>DetailTemplate);
        return psComplete;

    }

    /**
    * Выводит список элементов по id
    */
    public function action<?=$item; ?>ViewById() {
        $id = $this->get('<?=$lowerItem; ?>_id');
        $a<?=$item; ?> = models\<?=$item; ?>::find()
            ->where(['id'=>$id])
            ->asArray()->one();
        return $this-><?=$lowerItem; ?>ShowOne($a<?=$item; ?>);
    }

    /**
    * Выводит список элементов по alias
    */
    public function action<?=$item; ?>ViewByAlias() {
        $alias = $this->get('<?=$lowerItem; ?>_alias');
        $a<?=$item; ?> = models\<?=$item; ?>::find()
            ->where(['alias'=>$alias])
            ->asArray()->one();
        return $this-><?=$lowerItem; ?>ShowOne($a<?=$item; ?>);
    }

<?php endforeach; endif; ?>


}