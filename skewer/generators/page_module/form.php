<?php
/**
 * This is the form.
 *
 * @var yii\web\View
 * @var $form yii\widgets\ActiveForm
 * @var $generator \skewer\generators\page_module\Generator
 */
$aDictionary = \skewer\components\catalog\Dict::getDictArrayWithName(skewer\base\site\Layer::TOOL);

?>
<div class="module-form">
    <?php
    echo $form->field($generator, 'moduleName');
    echo $form->field($generator, 'nameDict')->dropDownList($aDictionary);
    echo $form->field($generator, 'bAddLabel')->checkbox();
    echo $form->field($generator, 'bInstall')->checkbox();
    ?>
</div>