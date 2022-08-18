<?php

/**
 * @param string $formHash
 * @param int $iRandVal
 */
?>
<div class="form__col-1">
    <div class="form__item form__item--captha form__item--label-top">
        <div class="form__label"><?=Yii::t('forms', 'captcha'); ?></div>
        <div class="form__input form__input--input">
            <img alt="" src="/ajax/captcha.php?h=<?=$formHash; ?>&v=<?=$iRandVal; ?>" class="form__captha-img img_captcha" />
            <input data-name="<?=Yii::t('forms', 'captcha_title'); ?>" type="text" value="" name="captcha" id="captcha" maxlength="4" />
        </div>
    </div>
</div>
