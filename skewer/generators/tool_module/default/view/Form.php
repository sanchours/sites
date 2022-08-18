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
    unset($descAR->columns['priority']);
}

echo "<?php\n";
?>
namespace <?= $ns; ?>;

use skewer\base\ft\Editor;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\ext\view\FormView;

class <?php if (isset($modelName)): ?><?=$modelName; ?><?php endif; ?>Form extends FormView{

    /**@var ActiveRecord */
    public $item;

    /**
     * Выполняет сборку интерфейса
     * @return void
     */
    function build() {


        $this->_form
<?php foreach ($descAR->columns as $key => $column):?>
    <?php if ($key == 'id'):?>
                ->field('<?=$column->name; ?>', \Yii::t('<?=$languageCategory; ?>', 'field_<?=$column->name; ?>'), 'hide')
    <?php else: ?>
                ->field('<?=$column->name; ?>', \Yii::t('<?=$languageCategory; ?>', 'field_<?=$column->name; ?>'),  Editor::<?=mb_strtoupper($column->type); ?>, ['listColumns' => ['flex' => 1]])
    <?php endif; ?>
<?php endforeach; ?>

            ->buttonSave(<?php if (isset($modelName)): ?>'<?=$modelName; ?>Save'<?php endif; ?>)
            ->buttonBack(<?php if (isset($modelName)): ?>'<?=$modelName; ?>List'<?php endif; ?>);

        if (!$this->item->getIsNewRecord()) {
<?php if (!isset($modelName)):?>
            $this->_form
    <?php if (!empty($generator->aNameARs)):?>
        <?php foreach ($generator->aNameARs as $key => $item):?>
                ->buttonEdit('<?=$item; ?>List',\Yii::t('<?=$languageCategory; ?>', 'button_<?=mb_strtolower($item); ?>'))
        <?php endforeach; ?>
    <?php endif; ?>
            ;
<?php endif; ?>

            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete(<?php if (isset($modelName)): ?>'<?=$modelName; ?>Delete'<?php endif; ?>);
        }

        $this->_form->setValue($this->item);

    }
}