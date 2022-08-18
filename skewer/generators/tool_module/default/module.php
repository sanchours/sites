<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\tool_module\Generator
 * @var $descARs yii\db\TableSchema
 */
    $moduleName = $generator->moduleName;
    $nameAR = $generator->nameAR;
    $fullNameAR = $generator->fullNameAR;
    $pathARs = $generator->pathARs;
    $aNameARs = $generator->aNameARs;
    $languageCategory = mb_strtolower($moduleName);
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Tool\\' . $moduleName;
    echo "<?php\n";
?>

namespace <?= $ns; ?>;

use skewer\build\Tool\LeftList\ModulePrototype;
use <?=$fullNameAR; ?>;
<?php if ($generator->aNameARs !== []):
    foreach ($generator->aNameARs as $item):
?>
use <?=$pathARs; ?>\<?=$item; ?>;
<?php endforeach; endif; ?>
use skewer\build\Tool\<?= $moduleName; ?>\view;
use yii\base\UserException;
use skewer\base\ui\ARSaveException;

/**
 *  Class Module
 * @package skewer\build\Tool\<?= $moduleName . "\n"; ?>
 */
class Module extends ModulePrototype{

    // текущий номер страницы ( с 0, а приходит с 1 )
    public $iPageNum = 0;
    // число элементов на страниц
    public $iOnPage = 20;

    //переменные для перехода по вложенным сущностям
    public $i<?=$nameAR; ?> = 0;
<?php if ($generator->aNameARs !== []):
    foreach ($generator->aNameARs as $item):?>
    public $i<?=$item; ?> = 0;
<?php endforeach; endif; ?>

    protected function preExecute() {

        // номер страницы
        $this->iPageNum = $this->getInt('page');

    }

    protected function actionInit() {
        $this->actionList();
    }


    /**
    * Выводит список элементов из сущности AR <?=$nameAR; ?>

    */
    public function actionList() {

        $this->i<?=$nameAR; ?> = 0;

        $query = <?=$nameAR; ?>::find()
            ->limit($this->iOnPage)
            ->offset($this->iPageNum * $this->iOnPage);

<?php if (isset($descARs[$nameAR]->columns['priority'])): ?>
        $query->orderBy('priority');
<?php endif; ?>
        $aItems = $query->asArray()
                        ->all();

        $iCount = <?=$nameAR; ?>::find()
            ->count();

        $this->setPanelName(\Yii::t('<?=$languageCategory; ?>','list'));
        $this->render(new view\Index([
            'aItems' => $aItems,
            'page' => $this->iPageNum,
            'onPage' => $this->iOnPage,
            'total' => $iCount
        ]));

    }


    /**
    * Выводит форму для добавления/редактирования объекта AR <?=$nameAR; ?>

    */
    public function actionForm() {

        $aData = $this->getInData();

        if(isset($aData['id']) || $this->i<?=$nameAR; ?>!= 0){
            $this->i<?=$nameAR; ?> = $this->i<?=$nameAR; ?>?:$aData['id'];
            $o<?=$nameAR; ?>  = <?=$nameAR; ?>::findOne(['id' => $this->i<?=$nameAR; ?>]);
            $sPanelName = \Yii::t('<?=$languageCategory; ?>', 'edit_item', $o<?=$nameAR; ?>->title);
        }else {
            $o<?=$nameAR; ?>  = new <?=$nameAR; ?>();
            $sPanelName = \Yii::t('<?=$languageCategory; ?>', 'new_item');
        }

        $this->setPanelName($sPanelName);
        $this->render(new view\Form(['item' => $o<?=$nameAR; ?>]));

    }

    /**
    * Добавление/сохранение изменений объекта AR <?=$nameAR; ?>

    * @throws UserException
    * @throws ARSaveException
    * @throws \yii\db\Exception
    */
    public function actionSave(){

        // запросить данные
        $aData = $this->get( 'data', [] );
        $iId = $this->getInDataValInt( 'id' );

        // Новая запись?
        $bIsNewRecord = !(bool)$iId;

        if ( !$bIsNewRecord ) {
            $o<?=$nameAR; ?>Row = <?=$nameAR; ?>::findOne(['id' => $iId] );
            if ( !$o<?=$nameAR; ?>Row){
                throw new UserException( \Yii::t('<?=$languageCategory; ?>', 'error_row_not_found', [$iId]));
            }
        } else {
            $o<?=$nameAR; ?>Row = new <?=$nameAR; ?>();
<?php if (isset($descARs[$nameAR]->columns['priority'])): ?>

            $aData[Api::FIELD_SORT] = Api::getMaxPriority(<?=$nameAR; ?>::tableName());
<?php endif; ?>
        }

        // Запомним данные до внесения изменений
        $aOldAttributes = $o<?=$nameAR; ?>Row->getAttributes();

        // Заполняем запись данными из web-интерфейса
        $o<?=$nameAR; ?>Row->setAttributes($aData);

        if (!$o<?=$nameAR; ?>Row->save()){
            throw new ARSaveException( $o<?=$nameAR; ?>Row );
        }

        $this->actionList();

    }

    /**
    * Удаление объекта AR <?=$nameAR; ?>

    * @throws UserException
    * @throws \Exception
    * @throws \yii\db\StaleObjectException
    */
    public function actionDelete(){

        // запросить данные
        $iItemId = $this->getInDataValInt( 'id', 0 );
        $o<?=$nameAR; ?> = <?=$nameAR; ?>::findOne($iItemId);
        if (!$o<?=$nameAR; ?>){
            throw new UserException(\Yii::t('<?=$languageCategory; ?>','error_row_not_found',[$iItemId]));
        }

        $o<?=$nameAR; ?>->delete();

        // вывод списка
        $this->actionInit();

    }

<?php if (isset($descARs[$nameAR]->columns['priority'])): ?>

    public function actionSort(){

        $aDropData = $this->get( 'dropData' );
        $sPosition = $this->get( 'position' );

        $iDropId = isset( $aDropData['id'] ) ? $aDropData['id'] : false;
        $aItems = [$this->getInDataValInt( 'id', 0 )];

        if( !count($aItems) || !$iDropId || !$sPosition ){
            $this->addError(\Yii::t('<?=$languageCategory; ?>','error_sort'));
        }

        if ( $sPosition == 'after' ){
            $aItems = array_reverse($aItems);
        }

        foreach ( $aItems as $iSelectId ){
            Api::sortItems(new <?=$nameAR; ?>(),$iSelectId,$iDropId,$sPosition);
        }

    }
<?php endif; ?>

<?php
// добавление функций для дополнительных моделей
if ($generator->aNameARs !== []):
    foreach ($generator->aNameARs as $item):
        $lowerItem = mb_strtolower($item);
   //foreach
?>

        /**
        * Выводит список элементов из сущности AR <?=$item; ?>

        */
        public function action<?=$item; ?>List() {

            $this->i<?=$item; ?> = 0;

            $query = <?=$item; ?>::find()
                ->limit($this->iOnPage)
                ->offset($this->iPageNum * $this->iOnPage);
        <?php if (isset($descARs[$item]->columns['priority'])): ?>
            $query->orderBy('priority');
        <?php endif; ?>

            $aItems = $query->asArray()->all();
            $iCount = <?=$item; ?>::find()->count();

            $this->setPanelName(\Yii::t('<?=$languageCategory; ?>','list_<?=$lowerItem; ?>'));
            $this->render(new view\<?=$item; ?>Index([
                'aItems' => $aItems,
                'page' => $this->iPageNum,
                'onPage' => $this->iOnPage,
                'total' => $iCount
            ]));

        }

        /**
        * Выводит форму для добавления/редактирования объекта AR <?=$item; ?>

        */
        public function action<?=$item; ?>Form() {

            $aData = $this->getInData();

            if(isset($aData['id']) || $this->i<?=$item; ?>!=0){
                $this->i<?=$item; ?> = $this->i<?=$item; ?>?:$aData['id'];
                $o<?=$item; ?>  = <?=$item; ?>::findOne(['id' => $this->i<?=$item; ?>]);
                $sPanelName = (\Yii::t('<?=$languageCategory; ?>','edit_item_<?=$lowerItem; ?>', $o<?=$item; ?>->title));
            }else {
                $o<?=$item; ?>= new <?=$item; ?>();
                $sPanelName = (\Yii::t('<?=$languageCategory; ?>','new_item_<?=$lowerItem; ?>'));
            }

            $this->setPanelName($sPanelName);
            $this->render(new view\<?=$item; ?>Form(['item' => $o<?=$item; ?>]));
        }

        /**
        * Добавление/сохранение изменений объекта AR <?=$item; ?>

        * @throws UserException
        * @throws ARSaveException
        * @throws \yii\db\Exception
        */
        public function action<?=$item; ?>Save(){

            // запросить данные
            $aData = $this->get( 'data', [] );
            $iId = $this->getInDataValInt( 'id' );

            // Новая запись?
            $bIsNewRecord = !(bool)$iId;

            if ( !$bIsNewRecord ) {
                $o<?=$item; ?>Row = <?=$item; ?>::findOne(['id' => $iId]);
                if (!$o<?=$item; ?>Row){
                    throw new UserException( \Yii::t('<?=$languageCategory; ?>', 'error_row_not_found', [$iId]));
                }
            } else {
                $o<?=$item; ?>Row = new <?=$item; ?>();
        <?php if (isset($descARs[$item]->columns['priority'])): ?>

                $aData[Api::FIELD_SORT] = Api::getMaxPriority(<?=$item; ?>::tableName());
        <?php endif; ?>
            }

            // Запомним данные до внесения изменений
            $aOldAttributes = $o<?=$item; ?>Row->getAttributes();

            // Заполняем запись данными из web-интерфейса
            $o<?=$item; ?>Row->setAttributes($aData);

            if ( !$o<?=$item; ?>Row->save() ){
                throw new ARSaveException( $o<?=$item; ?>Row );
            }

            $this->action<?=$item; ?>List();
        }


        /**
        * Удаление объекта(ов) AR <?=$item; ?>

        * @throws UserException
        * @throws \Exception
        * @throws \yii\db\StaleObjectException
        */
        public function action<?=$item; ?>Delete(){

            // запросить данные
            $iItemId = $this->getInDataValInt( 'id', 0 );
            $o<?=$item; ?>= <?= $item; ?>::findOne($iItemId);
            if (!$o<?=$item; ?>){
                throw new UserException(\Yii::t('<?=$languageCategory; ?>','error_row_not_found',[$iItemId]));
            }

            $o<?=$item; ?>->delete();

            // вывод списка
            $this->action<?=$item; ?>List();

        }

        <?php if (isset($descARs[$item]->columns['priority'])): ?>

        public function action<?=$item; ?>Sort(){

            $aDropData = $this->get( 'dropData' );
            $sPosition = $this->get( 'position' );

            $iDropId = isset( $aDropData['id'] ) ? $aDropData['id'] : false;
            $aItems = [$this->getInDataValInt( 'id', 0 )];

            if( !count($aItems) || !$iDropId || !$sPosition ){
                $this->addError(\Yii::t('<?=$languageCategory; ?>','error_sort'));
            }

            if ( $sPosition == 'after' ){
                $aItems = array_reverse($aItems);
            }

            foreach ( $aItems as $iSelectId ){
                Api::sortItems(new <?=$item; ?>(),$iSelectId,$iDropId,$sPosition);
            }
        }
        <?php endif; ?>

<?php endforeach; endif; ?>

}