<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.09.2016
 * Time: 9:51.
 */

namespace skewer\components\content_generator;

class Config
{
    public static function getItems()
    {
        return [
            'templates' => [
                'one_caption' => [
                    'icon' => '/img/content_generator/main.gen1.jpg',
                    'title' => 'One caption',
                    'name' => 'one_caption',
                    'template' => 'one_caption.php',
                    'group' => 'caption',
                    'js' => [
                    ],
                    'css' => [
                    ],
                    'items' => [],
                ],
                'two_captions' => [
                    'icon' => '/img/content_generator/main.gen3.jpg',
                    'title' => 'Two caption',
                    'name' => 'two_captions',
                    'template' => 'two_caption.php',
                    'group' => 'caption',
                    'items' => [],
                    'css' => [
                        'css/super_test2.css',
                    ],
                ],
                'three_caption' => [
                    'icon' => '/img/content_generator/main.gen2.jpg',
                    'title' => 'Three caption',
                    'name' => 'three_caption',
                    'template' => 'three_caption.php',
                    'group' => 'caption',
                    'items' => [],
                ],

                'one_text_block' => [
                    'icon' => '/img/content_generator/main.gen4.jpg',
                    'title' => 'One text block',
                    'name' => 'one_text_block',
                    'template' => 'one_text_block.php',
                    'group' => 'text_block',
                    'items' => [],
                ],
                'two_text_block' => [
                    'icon' => '/img/content_generator/main.gen5.jpg',
                    'title' => 'Two text block',
                    'name' => 'two_text_block',
                    'template' => 'two_text_block.php',
                    'group' => 'text_block',
                    'items' => [],
                ],
                'three_text_block' => [
                    'icon' => '/img/content_generator/main.gen6.jpg',
                    'title' => 'Three text block',
                    'name' => 'three_text_block',
                    'template' => 'three_text_block.php',
                    'group' => 'text_block',
                    'items' => [],
                ],

                'one_decor_text' => [
                    'icon' => '/img/content_generator/main.gen7.jpg',
                    'title' => 'One decor text',
                    'name' => 'one_decor_text',
                    'template' => 'one_decor_text.php',
                    'group' => 'decor_text',
                    'items' => [],
                ],
                'two_decor_text' => [
                    'icon' => '/img/content_generator/main.gen8.jpg',
                    'title' => 'Two decor text',
                    'name' => 'two_decor_text',
                    'template' => 'two_decor_text.php',
                    'group' => 'decor_text',
                    'items' => [],
                ],
                'three_decor_text' => [
                    'icon' => '/img/content_generator/main.gen9.jpg',
                    'title' => 'Three decor text',
                    'name' => 'three_decor_text',
                    'template' => 'three_decor_text.php',
                    'group' => 'decor_text',
                    'items' => [],
                ],
                'four_decor_text' => [
                    'icon' => '/img/content_generator/main.gen12.jpg',
                    'title' => 'Four decor text',
                    'name' => 'four_decor_text',
                    'template' => 'four_decor_text.php',
                    'group' => 'decor_text',
                    'items' => [],
                ],

                'one_img_video' => [
                    'icon' => '/img/content_generator/main.gen13.jpg',
                    'title' => 'One img video',
                    'name' => 'one_img_video',
                    'template' => 'one_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],
                'two_img_video' => [
                    'icon' => '/img/content_generator/main.gen14.jpg',
                    'title' => 'Two img video',
                    'name' => 'two_img_video',
                    'template' => 'two_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],
                'three_img_video' => [
                    'icon' => '/img/content_generator/main.gen15.jpg',
                    'title' => 'Three img video',
                    'name' => 'three_img_video',
                    'template' => 'three_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],
                'four_img_video' => [
                    'icon' => '/img/content_generator/main.gen16.jpg',
                    'title' => 'Four img video',
                    'name' => 'four_img_video',
                    'template' => 'four_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],
                'fife_img_video' => [
                    'icon' => '/img/content_generator/main.gen17.jpg',
                    'title' => 'Fife img video',
                    'name' => 'fife_img_video',
                    'template' => 'fife_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],
                'six_img_video' => [
                    'icon' => '/img/content_generator/main.gen18.jpg',
                    'title' => 'Six img video',
                    'name' => 'six_img_video',
                    'template' => 'six_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],
                'seven_img_video' => [
                    'icon' => '/img/content_generator/main.gen19.jpg',
                    'title' => 'Seven img video',
                    'name' => 'seven_img_video',
                    'template' => 'seven_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],
                'eight_img_video' => [
                    'icon' => '/img/content_generator/main.gen20.jpg',
                    'title' => 'Eight img video',
                    'name' => 'eight_img_video',
                    'template' => 'eight_img_video.php',
                    'group' => 'img_and_video',
                    'items' => [],
                ],

                'one_table' => [
                    'icon' => '/img/content_generator/main.gen24.jpg',
                    'title' => 'One tables',
                    'name' => 'one_table',
                    'template' => 'one_table.php',
                    'group' => 'table',
                    'items' => [],
                ],
                'two_table' => [
                    'icon' => '/img/content_generator/main.gen22.jpg',
                    'title' => 'Two tables',
                    'name' => 'two_table',
                    'template' => 'two_table.php',
                    'group' => 'table',
                    'items' => [],
                ],
                'three_table' => [
                    'icon' => '/img/content_generator/main.gen21.jpg',
                    'title' => 'Three tables',
                    'name' => 'three_table',
                    'template' => 'three_table.php',
                    'group' => 'table',
                    'items' => [],
                ],

                'one_block_advantage' => [
                    'icon' => '/img/content_generator/main.gen25.jpg',
                    'title' => 'One block advantage',
                    'name' => 'one_block_advantage',
                    'template' => 'one_block_advantage.php',
                    'group' => 'block_advantage',
                    'items' => [],
                ],
                'two_block_advantage' => [
                    'icon' => '/img/content_generator/main.gen26.jpg',
                    'title' => 'Two block advantage',
                    'name' => 'two_block_advantage',
                    'template' => 'two_block_advantage.php',
                    'group' => 'block_advantage',
                    'items' => [],
                ],
                'three_block_advantage' => [
                    'icon' => '/img/content_generator/main.gen27.jpg',
                    'title' => 'Three block advantage',
                    'name' => 'three_block_advantage',
                    'template' => 'three_block_advantage.php',
                    'group' => 'block_advantage',
                    'items' => [],
                ],
                'advantage4_with_background' => [
                    'icon' => '/img/content_generator/main.gen38.jpg',
                    'title' => 'Advantage(4 block) with background',
                    'name' => 'advantage4_with_background',
                    'template' => 'advantage4_with_background.php',
                    'group' => 'block_advantage',
                    'items' => [],
                ],
                'advantage3_with_background' => [
                    'icon' => '/img/content_generator/main.gen39.png',
                    'title' => 'Advantage(3 block) with background',
                    'name' => 'advantage3_with_background',
                    'template' => 'advantage3_with_background.php',
                    'group' => 'block_advantage',
                    'items' => [],
                ],
                'advantage3_1_with_background' => [
                    'icon' => '/img/content_generator/main.gen40.jpg',
                    'title' => 'Advantage(3 block) with background',
                    'name' => 'advantage3_1_with_background',
                    'template' => 'advantage3_1_with_background.php',
                    'group' => 'block_advantage',
                    'items' => [],
                ],

                'one_promo_block' => [
                    'icon' => '/img/content_generator/main.gen28.jpg',
                    'title' => 'One promo block',
                    'name' => 'one_promo_block',
                    'template' => 'one_promo_block.php',
                    'group' => 'promo_block',
                    'items' => [],
                ],
                'two_promo_block' => [
                    'icon' => '/img/content_generator/main.gen29.jpg',
                    'title' => 'Two promo block',
                    'name' => 'two_promo_block',
                    'template' => 'two_promo_block.php',
                    'group' => 'promo_block',
                    'items' => [],
                ],
                'three_promo_block' => [
                    'icon' => '/img/content_generator/main.gen30.jpg',
                    'title' => 'Three promo block',
                    'name' => 'three_promo_block',
                    'template' => 'three_promo_block.php',
                    'group' => 'promo_block',
                    'items' => [],
                ],
                'four_promo_block' => [
                    'icon' => '/img/content_generator/main.gen31.jpg',
                    'title' => 'Four promo block',
                    'name' => 'four_promo_block',
                    'template' => 'four_promo_block.php',
                    'group' => 'promo_block',
                    'items' => [],
                ],
                'fife_promo_block' => [
                    'icon' => '/img/content_generator/main.gen32.jpg',
                    'title' => 'Fife promo block',
                    'name' => 'fife_promo_block',
                    'template' => 'fife_promo_block.php',
                    'group' => 'promo_block',
                    'items' => [],
                ],
                'six_promo_block' => [
                    'icon' => '/img/content_generator/main.gen34.jpg',
                    'title' => 'Six promo block',
                    'name' => 'six_promo_block',
                    'template' => 'six_promo_block.php',
                    'group' => 'promo_block',
                    'items' => [],
                ],
                'seven_promo_block' => [
                    'icon' => '/img/content_generator/main.gen33.jpg',
                    'title' => 'Seven promo block',
                    'name' => 'seven_promo_block',
                    'template' => 'seven_promo_block.php',
                    'group' => 'promo_block',
                    'items' => [],
                ],

                'one_baner' => [
                    'icon' => '/img/content_generator/main.gen35.jpg',
                    'title' => 'One baner',
                    'name' => 'one_baner',
                    'template' => 'one_baner.php',
                    'group' => 'baner',
                    'items' => [],
                ],
                'two_baner' => [
                    'icon' => '/img/content_generator/main.gen36.jpg',
                    'title' => 'Two baner',
                    'name' => 'two_baner',
                    'template' => 'two_baner.php',
                    'group' => 'baner',
                    'items' => [],
                ],
                'three_baner' => [
                    'icon' => '/img/content_generator/main.gen37.jpg',
                    'title' => 'Three baner',
                    'name' => 'three_baner',
                    'template' => 'three_baner.php',
                    'group' => 'baner',
                    'items' => [],
                ],

                'one_see_also' => [
                    'icon' => '/img/content_generator/main.gen41.jpg',
                    'title' => 'One see also',
                    'name' => 'one_see_also',
                    'template' => 'one_see_also.php',
                    'group' => 'see_also',
                    'items' => [],
                ],
            ],
        ];
    }

    public static function getGroups()
    {
        return [
            'caption' => [
                'icon' => '/img/content_generator/gen_icon1.png',
                'title' => \Yii::t('content_generator', 'caption_title'),
                'name' => 'caption',
            ],
            'text_block' => [
                'icon' => '/img/content_generator/gen_icon2.png',
                'title' => \Yii::t('content_generator', 'text_block_title'),
                'name' => 'text_block',
            ],
            'decor_text' => [
                'icon' => '/img/content_generator/gen_icon3.png',
                'title' => \Yii::t('content_generator', 'decor_text_title'),
                'name' => 'decor_text',
            ],
            'img_and_video' => [
                'icon' => '/img/content_generator/gen_icon4.png',
                'title' => \Yii::t('content_generator', 'img_and_video_title'),
                'name' => 'img_and_video',
            ],
            'table' => [
                'icon' => '/img/content_generator/gen_icon5.png',
                'title' => \Yii::t('content_generator', 'table_title'),
                'name' => 'table',
            ],
            'block_advantage' => [
                'icon' => '/img/content_generator/gen_icon6.png',
                'title' => \Yii::t('content_generator', 'block_advantages_title'),
                'name' => 'block_advantage',
            ],
            'promo_block' => [
                'icon' => '/img/content_generator/gen_icon7.png',
                'title' => \Yii::t('content_generator', 'promo_block_title'),
                'name' => 'promo_block',
            ],
            'baner' => [
                'icon' => '/img/content_generator/gen_icon8.png',
                'title' => \Yii::t('content_generator', 'banner_title'),
                'name' => 'baner',
            ],
            'see_also' => [
                'icon' => '/img/content_generator/gen_icon_see_more.png',
                'title' => \Yii::t('content_generator', 'see_also_title'),
                'name' => 'see_also',
            ],
        ];
    }
}
