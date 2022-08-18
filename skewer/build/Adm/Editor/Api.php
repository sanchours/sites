<?php

namespace skewer\build\Adm\Editor;

class Api
{
    /**
     * Возвращает дополнительные языковые данные для висисиг-редактора.
     *
     * @return array
     */
    public static function getAddLangParams4Wyswyg()
    {
        return [
            'format' => [
                'tag_icons_pdf' => \Yii::t('editor', 'wyswyg_format_tag_icons_pdf'),
                'tag_icons_zip' => \Yii::t('editor', 'wyswyg_format_tag_icons_zip'),
                'tag_icons_doc' => \Yii::t('editor', 'wyswyg_format_tag_icons_doc'),
                'tag_icons_xls' => \Yii::t('editor', 'wyswyg_format_tag_icons_xls'),
                'tag_icons_ppt' => \Yii::t('editor', 'wyswyg_format_tag_icons_ppt'),
                'tag_icons_disk' => \Yii::t('editor', 'wyswyg_format_tag_icons_disk'),
                'tag_icons_info' => \Yii::t('editor', 'wyswyg_format_tag_icons_info'),
                'tag_icons_warning' => \Yii::t('editor', 'wyswyg_format_tag_icons_warning'),
                'tag_icons_stickynote' => \Yii::t('editor', 'wyswyg_format_tag_icons_stickynote'),
                'tag_icons_download' => \Yii::t('editor', 'wyswyg_format_tag_icons_download'),
                'tag_icons_faq' => \Yii::t('editor', 'wyswyg_format_tag_icons_faq'),
                'tag_icons_flag' => \Yii::t('editor', 'wyswyg_format_tag_icons_flag'),
            ],
        ];
    }
}
