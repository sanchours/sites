<?php
/** @var int $svSize */
/** @var int $svSpace */
/** @var int $svNoimg */
/** @var int $svColor */
/** @var string $action */
?>
<?php  if (\skewer\build\Page\BlindVersion\Api::isBlindVersion()): ?>
    <?\skewer\build\Page\BlindVersion\Asset::register(Yii::$app->view); ?>
<div class="b-sppanel hide-on-tablet hide-on-mobile">
    <div class="sppanel__col">
        <div class="sppanel__btnbox">
            <div class="sppanel__btn">Настройки</div>
            <div class="sppanel__prop">
                <div class="sppanel__prop-header">
                    <h2>Настройки</h2>
                </div>
                <form class="js_blind_form" method="post">
                    <div class="sppanel__prop-inner">
                        <div class="sppanel__prop-row">
                            <div class="sppanel__prop-col sppanel__prop-colleft">

                                <p class="sppanel__title">Размер шрифта:</p>
                                <div class="sppanel__item sppanel__item-size1">
                                    <input type="radio" name="svSize" <?php if ($svSize == '1'):?>checked<?php endif; ?> value="1" id="sppanel-radio-0" />
                                    <label class="sppanel__label" for="sppanel-radio-0">14</label>
                                </div>
                                <div class="sppanel__item sppanel__item-size2">
                                    <input type="radio" name="svSize" <?php if ($svSize == '2'):?>checked<?php endif; ?> value="2" id="sppanel-radio-1" />
                                    <label class="sppanel__label" for="sppanel-radio-1">20</label>
                                </div>
                                <div class="sppanel__item sppanel__item-size3">
                                    <input type="radio" name="svSize" <?php if ($svSize == '3'):?>checked<?php endif; ?> value="3" id="sppanel-radio-2" />
                                    <label class="sppanel__label" for="sppanel-radio-2">28</label>
                                </div>

                            </div>
                            <div class="sppanel__prop-col sppanel__prop-colright">
                                <p class="sppanel__title">Интервал между буквами (Кернинг):</p>
                                <div class="sppanel__item sppanel__item-space1">
                                    <input type="radio" name="svSpace" <?php if ($svSpace == '1'):?>checked<?php endif; ?> value="1" id="sppanel-radio-3" />
                                    <label class="sppanel__label" for="sppanel-radio-3">Стандартный</label>
                                </div>
                                <div class="sppanel__item sppanel__item-space2">
                                    <input type="radio" name="svSpace" <?php if ($svSpace == '2'):?>checked<?php endif; ?> value="2" id="sppanel-radio-4" />
                                    <label class="sppanel__label" for="sppanel-radio-4">Средний</label>
                                </div>
                                <div class="sppanel__item sppanel__item-space3">
                                    <input type="radio" name="svSpace" <?php if ($svSpace == '3'):?>checked<?php endif; ?> value="3" id="sppanel-radio-5" />
                                    <label class="sppanel__label" for="sppanel-radio-5">Большой</label>
                                </div>
                            </div>
                            <div class="sppanel__prop-col sppanel__prop-colleft">
                                <p class="sppanel__title">Изображения:</p>
                                <div class="sppanel__item">
                                    <input type="radio" name="svNoimg" <?php if ($svNoimg == 1):?>checked<?php endif; ?> value="1" id="sppanel-radio-6" />
                                    <label class="sppanel__label"   for="sppanel-radio-6">Включены</label>
                                </div>
                                <div class="sppanel__item">
                                    <input type="radio" name="svNoimg" <?php if ($svNoimg == 2):?>checked<?php endif; ?> value="2" id="sppanel-radio-7" />
                                    <label class="sppanel__label" for="sppanel-radio-7">Выключены</label>
                                </div>
                            </div>
                            <div class="sppanel__prop-col sppanel__prop-colright">
                                <p class="sppanel__title">Цветовая схема:</p>
                                <div class="sppanel__item sppanel__item-color1">
                                    <input type="radio" name="svColor" <?php if ($svColor == 'white'):?>checked<?php endif; ?> value="white" id="sppanel-radio-8" />
                                    <label class="sppanel__label" for="sppanel-radio-8"><span class="sppanel__label-inner">Черным по белому</span></label>
                                </div>
                                <div class="sppanel__item sppanel__item-color2">
                                    <input type="radio" name="svColor" <?php if ($svColor == 'black'):?>checked<?php endif; ?> value="black" id="sppanel-radio-9" />
                                    <label class="sppanel__label" for="sppanel-radio-9"><span class="sppanel__label-inner">Белым по черному</span></label>
                                </div>
                                <div class="sppanel__item sppanel__item-color3">
                                    <input type="radio" name="svColor" <?php if ($svColor == 'blue'):?>checked<?php endif; ?> value="blue" id="sppanel-radio-10" />
                                    <label class="sppanel__label" for="sppanel-radio-10"><span class="sppanel__label-inner">Тёмно-синим по голубому</span></label>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="sppanel__col">
        <div class="sppanel__wordbox">
            <span class="sppanel__word-1"><a href="?svSize=3">a</a></span>
            <span class="sppanel__word-2"><a href="?svSize=2">a</a></span>
            <span class="sppanel__word-3"><a href="?svSize=1">a</a></span>
        </div>
    </div>
    <div class="sppanel__col">
        <div class="sppanel__colorbox">
            <a href="?svColor=black" class="sppanel__color-1"></a>
            <a href="?svColor=blue" class="sppanel__color-2"></a>
            <a href="?svColor=white" class="sppanel__color-3"></a>
        </div>
    </div>
</div>

<?endif; ?>
<?php if (isset($blindSwitcher)): ?>
    <?=$blindSwitcher; ?>
<?php endif; ?>