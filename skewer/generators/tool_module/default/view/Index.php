<?php
/**
 * This is the template for generating config file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\tool_module\Generator
 * @var $descAR yii\db\TableSchema
 * @var $modelName string
 */
$moduleName = $generator->moduleName;
$languageCategory = mb_strtolower($generator->moduleName);
$fullClassName = $generator->getModulePath();
$ns = 'skewer\build\Tool\\' . $moduleName . '\view';
$sLayer = skewer\base\site\Layer::TOOL;

if (isset($descAR->columns['priority'])) {
    $sort = 1;
    unset($descAR->columns['priority']);
}

echo "<?php\n";
?>
namespace <?= $ns; ?>;

use skewer\base\ft\Editor;
use skewer\components\ext\view\ListView;

class <?php if (isset($modelName)): ?><?=$modelName; ?><?php endif; ?>Index extends ListView {

    public $aItems = [];

    /**
     * @inheritdoc
     */
    function build() {

        $this->_list
<?php foreach ($descAR->columns as $column):?>
                ->field('<?=$column->name; ?>', \Yii::t('<?=$languageCategory; ?>', 'field_<?=$column->name; ?>'),  Editor::<?=mb_strtoupper($column->type); ?>, ['listColumns' => ['flex' => 1]])
<?php endforeach; ?>
            ->buttonRowUpdate('<?php if (isset($modelName)): ?><?=$modelName; ?><?php endif; ?>Form')
            ->buttonRowDelete()
            ->buttonAddNew('<?php if (isset($modelName)): ?><?=$modelName; ?><?php endif; ?>Form');

<?php if (isset($modelName) && !empty($generator->aNameARs)):?>
        $this->_list
            ->buttonBack('form')
        ;
<?php endif; ?>
<?php if (isset($sort)): ?>
        $this->_list->enableDragAndDrop('<?php if (isset($modelName)): ?><?=$modelName; ?><?php endif; ?>Sort');
<?php endif; ?>

        $this->_list->setValue($this->aItems, $this->onPage, $this->page, $this->total);

    }

}