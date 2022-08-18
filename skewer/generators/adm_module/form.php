<?php
/**
 * This is the form.
 *
 * @var yii\web\View
 * @var $form yii\widgets\ActiveForm
 * @var $generator \skewer\generators\adm_module\Generator
 */
?>
<div class="module-form">
    <?php
    echo $form->field($generator, 'moduleName');
    echo $form->field($generator, 'fullNameAR');
    echo $form->field($generator, 'pathARs');
    echo $form->field($generator, 'moduleDescription');
    echo $form->field($generator, 'moduleTitle');
    echo $form->field($generator, 'bInstall')->checkbox();
    ?>
</div>