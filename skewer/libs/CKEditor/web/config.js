/*
 Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    config.language = 'ru';
    config.uiColor = '#E9EEF5';
    config.dialog_backgroundCoverColor = '#CCCCCC';

    config.allowedContent = true;

    config.extraPlugins = 'mediaembed,video,UploadManager,widget,iframe,table,tabletools,tableresize,contenteditor,fontawesome,codemirror,popupform,tooltip2,tipograf,tipograf_all_text';

    config.removePlugins = 'magicline';

    config.disableNativeSpellChecker = false;

    config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre;address;div;info;warning;stickynote;'+
        'download;faq;flag;pdf;doc;xls;ppt;zip;disk;' +
        'icons_pdf;icons_zip;icons_doc;icons_xls;icons_ppt;icons_disk;' +
        'icons_info;icons_warning;icons_stickynote;icons_download;icons_faq;icons_flag'
    ;

    config.format_info = { element: 'p', attributes: { 'class': 't-box-info' } };
    config.format_warning = { element: 'p', attributes: { 'class': 't-box-warning' } };
    config.format_stickynote = { element: 'p', attributes: { 'class': 't-box-stickynote' } };
    config.format_download = { element: 'p', attributes: { 'class': 't-box-download' } };
    config.format_faq = { element: 'p', attributes: { 'class': 't-box-faq' } };
    config.format_flag = { element: 'p', attributes: { 'class': 't-box-flag' } };
    config.format_pdf = { element: 'p', attributes: { 'class': 't-box-pdf' } };
    config.format_doc = { element: 'p', attributes: { 'class': 't-box-doc' } };
    config.format_xls = { element: 'p', attributes: { 'class': 't-box-xls' } };
    config.format_ppt = { element: 'p', attributes: { 'class': 't-box-ppt' } };
    config.format_zip = { element: 'p', attributes: { 'class': 't-box-zip' } };
    config.format_disk = { element: 'p', attributes: { 'class': 't-box-disk' } };

    config.format_icons_pdf = { element: 'p', attributes: { 'class': 't-icons-pdf' } };
    config.format_icons_zip = { element: 'p', attributes: { 'class': 't-icons-zip' } };
    config.format_icons_doc = { element: 'p', attributes: { 'class': 't-icons-doc' } };
    config.format_icons_xls = { element: 'p', attributes: { 'class': 't-icons-xls' } };
    config.format_icons_ppt = { element: 'p', attributes: { 'class': 't-icons-ppt' } };
    config.format_icons_disk = { element: 'p', attributes: { 'class': 't-icons-disk' } };
    config.format_icons_info = { element: 'p', attributes: { 'class': 't-icons-info' } };
    config.format_icons_warning = { element: 'p', attributes: { 'class': 't-icons-warning' } };
    config.format_icons_stickynote = { element: 'p', attributes: { 'class': 't-icons-stickynote' } };
    config.format_icons_download = { element: 'p', attributes: { 'class': 't-icons-download' } };
    config.format_icons_faq = { element: 'p', attributes: { 'class': 't-icons-faq' } };
    config.format_icons_flag = { element: 'p', attributes: { 'class': 't-icons-flag' } };

    config.toolbar = [
        ['Source']
    ];

    config.toolbar.push(['Contenteditor']);
    config.toolbar.push(['FontAwesome']);

    if (CKEDITOR.lock_tooltip_module=='0'){
        config.toolbar.push(['tooltip2']);
    }

    config.toolbar.push(['popupform']);

    var tmp = [
        ['Maximize'],
        ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', 'tipograf', 'tipograf_all_text'],
        ['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', 'SpecialChar'],
        ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
        ['Subscript', 'Superscript'],
        ['TextColor', 'BGColor'],
        '/',
        ['Bold', 'Italic', 'Underline', 'Strike'],

        ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
        ['Link', 'Unlink', 'Anchor'],
        ['Image', 'Flash', 'Table', 'HorizontalRule'],
        ['Format', 'Font', 'FontSize'],
        ['MediaEmbed', 'video']

    ];

    for (var i = 0; i < tmp.length; i++) {

        config.toolbar.push(tmp[i]);
    }


};

CKEDITOR.config['filebrowserBrowseUrl']	= buildConfig.files_path+'?mode=fileBrowser&type=file&returnTo=ckeditor';

CKEDITOR.config.keystrokes=[
    [122 /*X*/, 'maximize' ]
];
