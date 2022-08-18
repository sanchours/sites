<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.09.2016
 * Time: 9:44.
 */
?> 
<section class="gc-tabs">    
<?php foreach ($groups as $key => $group) { ?>
    <?php if (isset($group['items'])) {?>

        <div class="tab_title" group_name="<?=$group['name']; ?>" onclick="operateGroup('<?=$group['name']; ?>')">
            <div class="tab__img"><img src="<?=\skewer\components\content_generator\Asset::getAssetImg($group['icon']); ?>"></div>
            <p><?=$group['title']; ?></p>
        </div>
        <div class="tabs_cont"> 
        <div class="tab_c<?=$key; ?> js_one_block" style="display:none" group="<?=$group['name']; ?>">
            <div class="tab_<?=$key; ?>">
                <div class="gc-imgbox"> 
                    <?php foreach ($group['items'] as $template) { ?>

                        <div class="imgbox__item" name="<?=$template['name']; ?>" ondblclick="insertDiv('<?=$template['name']; ?>','<?=Yii::$app->language; ?>')" onclick="checkDiv('<?=$template['name']; ?>','<?=Yii::$app->language; ?>');">
                                <img src="<?=skewer\components\content_generator\Asset::getAssetImg($template['icon']); ?>">
                                <div class="imgbox__title">
                                    <h3><?=$template['title']; ?></h3>
                                </div>
                        </div>

                    <?php } ?>
                </div>
            </div>
        </div> 
        </div>  
        <?php } ?>
    <?php } ?>
<section>

<style>
    <?php foreach ($css_paths as $item) {?>
    @import "<?=$item; ?>" screen;
    <?php } ?>
    /*------------------*/
    .gc-imgbox {
        margin: 20px -10px 40px;
        white-space: normal;
    }
    .gc-imgbox  .imgbox__item {
        margin: 0 10px 20px;
        display: inline-block;
        vertical-align: top;
        width: 270px;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        border: 1px solid #eaeaea;
    }

    .gc-imgbox  .imgbox__item:hover {
        cursor: pointer;
        border: 1px solid #b6c3cf;
    }
    .gc-imgbox .imgbox__title {
        display: none;
    }
    /*------------------*/
    .gc-imgbox-info .imgbox__title {
        display: block;
    }
    /*------------------*/
    /*------------------*/ 
    /* TABS */
    .gc-tabs {
        position: relative;
        margin: 0 auto;
        width: 100%;
        max-width: 1210px;
        min-width: 980px;
    }
    .gc-tabs .tabs__left {
        -webkit-box-shadow: 31px 1px 57px -5px rgba(233,233,233,0.96);
        -moz-box-shadow: 31px 1px 57px -5px rgba(233,233,233,0.96);
        box-shadow: 31px 1px 57px -5px rgba(233,233,233,0.96);
        border-right: 1px solid #f3f3f3;
    }
    .gc-tabs .tab_title {
        display: block;
        width: 25%;
        padding-left: 30px;
        box-sizing: border-box;
        cursor: pointer;
        position: relative;
        height: 45px;
        line-height: 45px;
        z-index: 1;
    }
    .gc-tabs .tab_title:hover {
        background: #f7f7f7;
    }
    .gc-tabs .tab_title:hover img {
        opacity: 1;
    }
    .gc-tabs .tab_title img {
        opacity: 0.5;
    }
    .gc-tabs  .tab__img {
        display: inline-block;
        width: 22px;
    }
    .gc-tabs .tab_title p {
        display: inline-block;
        color: #496a89;
        font-size: 14px;
        padding-left: 10px;
        cursor: pointer;
    }
    .gc-tabs .tab__wrap {
        display: inline-block;
        white-space: normal;
    }
    .gc-tabs input {
        position: absolute;
        left: -9999px;
    }
    .tabs_cont {
        -webkit-box-shadow: inset 24px 0 57px -20px rgba(233,233,233,0.5);
        -moz-box-shadow: inset 24px 0 57px -20px rgba(233,233,233,0.5);
        box-shadow: inset 24px 0 57px -20px rgba(233,233,233,0.5);
        background: #fff;
        padding: 0 35px 20px 50px;
        position: absolute;
        top: 0;
        left: 245px;
        width: 70%;
        box-sizing: border-box;
        overflow-x: hidden;
        z-index: 0;
        height: auto; 
    }
   
</style>
