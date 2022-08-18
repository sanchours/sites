/**
 * Библиотека для отображения слайда для баннера
 */
Ext.define('Ext.Tool.SlideShower',{

    extend: 'Ext.form.field.Base',

    border: 0,
    padding: 5,
    minWidth: 1000,
    width: 1280,

    /** @property {CKEDITOR.editor} */
    ckeditorInstances: {},

    addWyswygConfig: {},

    fieldSubTpl:  [
        '<div class="b-banner">' +
            '<div class="banner__item">' +
            '<div id="ban_dd_lab_1" class="banner__text1" style="' +
            '<tpl if="text1_h !== false">left: {text1_h}px;</tpl>' +
            '<tpl if="text1_v !== false">top: {text1_v}px;</tpl>' +
            '"><div id="text1" contenteditable="true" class="builder-show-field">{text1}</div><span class="js_sdd ban-dd">text1</span></div>' +
            '<div id="ban_dd_lab_2" class="banner__text2" style="' +
            '<tpl if="text2_h !== false">left: {text2_h}px;</tpl>' +
            '<tpl if="text2_v !== false">top: {text2_v}px;</tpl>' +
            '"><div id="text2" contenteditable="true" class="builder-show-field">{text2}</div><span class="js_sdd ban-dd">text2</span></div>' +
            '<div id="ban_dd_lab_3" class="banner__text3" style="' +
            '<tpl if="text3_h !== false">left: {text3_h}px;</tpl>' +
            '<tpl if="text3_v !== false">top: {text3_v}px;</tpl>' +
            '"><div id="text3" contenteditable="true" class="builder-show-field">{text3}</div><span class="js_sdd ban-dd">text3</span></div>' +
            '<div id="ban_dd_lab_4" class="banner__text4" style="' +
            '<tpl if="text4_h !== false">left: {text4_h}px;</tpl>' +
            '<tpl if="text4_v !== false">top: {text4_v}px;</tpl>' +
            '"><div id="text4" contenteditable="true" class="builder-show-field">{text4}</div><span class="js_sdd ban-dd">text4</span></div>' +
            '</div>'+
            '<img class="js_banner_bg" src="{img}" alt="{title}" {addAttr}></div>'
    ],
    listeners: {
        // кроп интерфейс вызывается только при необходимости
        afterrender: function( me ) {

            $("[id^=ban_dd_lab_]").draggable({
                containment:".b-banner",
                handle: ".js_sdd",
                stop: function(){
                    var cur_id = this.id.substr(11);
                    sk.removeCKEditorOnPlace( 'text' + cur_id );
                    sk.initCKEditorOnPlace( 'text' + cur_id, me.addWyswygConfig );
                }
            });

        },
        beforedestroy: function() {

            for( var i=1; i<5; i++ )
                sk.removeCKEditorOnPlace( 'text' + i );

            $("[id^=ban_dd_lab_]").remove();

        }
    },

    execute: function( data ) {

        if ( data[0]['addWyswygConfig'] )
            this.addWyswygConfig = data[0]['addWyswygConfig'];

        if (data[0]['contentsCss'])
            this.addWyswygConfig.contentsCss = data[0]['contentsCss'];

        if (data[0]['addLangParams'])
            this.addWyswygConfig.addLangParams = data[0]['addLangParams'];

        this.addWyswygConfig.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre;address;div;info;warning;stickynote;'+
            'download;faq;flag;pdf;doc;xls;ppt;zip;disk;' +
            'icons_pdf;icons_zip;icons_doc;icons_xls;icons_ppt;icons_disk;' +
            'icons_info;icons_warning;icons_stickynote;icons_download;icons_faq;icons_flag'
        ;

        this.addWyswygConfig.format_info = { element: 'p', attributes: { 'class': 't-box-info' } };
        this.addWyswygConfig.format_warning = { element: 'p', attributes: { 'class': 't-box-warning' } };
        this.addWyswygConfig.format_stickynote = { element: 'p', attributes: { 'class': 't-box-stickynote' } };
        this.addWyswygConfig.format_download = { element: 'p', attributes: { 'class': 't-box-download' } };
        this.addWyswygConfig.format_faq = { element: 'p', attributes: { 'class': 't-box-faq' } };
        this.addWyswygConfig.format_flag = { element: 'p', attributes: { 'class': 't-box-flag' } };
        this.addWyswygConfig.format_pdf = { element: 'p', attributes: { 'class': 't-box-pdf' } };
        this.addWyswygConfig.format_doc = { element: 'p', attributes: { 'class': 't-box-doc' } };
        this.addWyswygConfig.format_xls = { element: 'p', attributes: { 'class': 't-box-xls' } };
        this.addWyswygConfig.format_ppt = { element: 'p', attributes: { 'class': 't-box-ppt' } };
        this.addWyswygConfig.format_zip = { element: 'p', attributes: { 'class': 't-box-zip' } };
        this.addWyswygConfig.format_disk = { element: 'p', attributes: { 'class': 't-box-disk' } };

        this.addWyswygConfig.format_icons_pdf = { element: 'p', attributes: { 'class': 't-icons-pdf' } };
        this.addWyswygConfig.format_icons_zip = { element: 'p', attributes: { 'class': 't-icons-zip' } };
        this.addWyswygConfig.format_icons_doc = { element: 'p', attributes: { 'class': 't-icons-doc' } };
        this.addWyswygConfig.format_icons_xls = { element: 'p', attributes: { 'class': 't-icons-xls' } };
        this.addWyswygConfig.format_icons_ppt = { element: 'p', attributes: { 'class': 't-icons-ppt' } };
        this.addWyswygConfig.format_icons_disk = { element: 'p', attributes: { 'class': 't-icons-disk' } };
        this.addWyswygConfig.format_icons_info = { element: 'p', attributes: { 'class': 't-icons-info' } };
        this.addWyswygConfig.format_icons_warning = { element: 'p', attributes: { 'class': 't-icons-warning' } };
        this.addWyswygConfig.format_icons_stickynote = { element: 'p', attributes: { 'class': 't-icons-stickynote' } };
        this.addWyswygConfig.format_icons_download = { element: 'p', attributes: { 'class': 't-icons-download' } };
        this.addWyswygConfig.format_icons_faq = { element: 'p', attributes: { 'class': 't-icons-faq' } };
        this.addWyswygConfig.format_icons_flag = { element: 'p', attributes: { 'class': 't-icons-flag' } };

        var me = this;
        var img = $("img.js_banner_bg");
        $(me.bodyEl.dom).width( img.length ? img.width() : 1000);
        img.on( 'load', function() {
            $(me.bodyEl.dom).width( img.width() );
        });

        for( var i=1; i<5; i++ ){
            this.ckeditorInstances[i] = sk.initCKEditorOnPlace( 'text' + i, this.addWyswygConfig );
        }

    },

    /**
     * Определяет является ли браузер ie9
     * @return bool
     */
    isIE9: function() {
        return /msie 9\./.test(navigator.userAgent.toLowerCase());
    },

    initComponent: function() {

        var me = this;

        // хак для ie9
        if ( me.isIE9() ) {
            for( var i=1; i<=4; i++ ) {
                me.value['text'+i] = me.value['text'+i]+'&nbsp;&nbsp;';
            }
        }

        // если есть спец параметр - заменить стандартный
        if ( me.value )
            me.subTplData = me.value;

        me.callParent();

    },

    getSubmitData: function() {

        var data = {};
        var image_w = $(".js_banner_bg").width();
        var image_h = $(".js_banner_bg").height();

        for( var i=1; i<5; i++ ) {

            var block_y = parseInt( $('#ban_dd_lab_' + i).css('top') );
            var block_x = parseInt( $('#ban_dd_lab_' + i).css('left') );

            if ( block_x < 0 )
                block_x = -block_x;

            if ( block_x > image_w )
                block_x = parseInt( image_w * 0.8 );

            if ( block_y < 0 )
                block_y = -block_y;

            if ( block_y > image_h )
                block_y = parseInt( image_h * 0.8 );

//            if( (i > 2) && image_w )
//                block_x = image_w - $('#ban_dd_lab_' + i).width() - block_x;

            this.ckeditorInstances[i].updateElement();
            data['text' + i] = this.ckeditorInstances[i].getData();
            data['text' + i + '_v'] = block_y;
            data['text' + i + '_h'] = block_x;

        }

        return data;
    }


});
