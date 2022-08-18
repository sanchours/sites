CKEDITOR.dialog.add('video', function (editor) {

    var player = CKEDITOR.videoPath + 'video.swf';
    var bundleWidth = CKEDITOR.video.width;
    var bundleHeight = CKEDITOR.video.height;
    var bundleUiColor = CKEDITOR.video.uiColor;
    var bundleUiBgColor = CKEDITOR.video.uiBgColor;
    var bundleBgColor = CKEDITOR.video.bgColor;
    var defaultWidth, defaultHeight, defaultUiColor, defaultUiBgColor, defaultBgColor, defaultPlaybutton;
    bundleWidth == null ? defaultWidth = '480' : defaultWidth = bundleWidth;
    bundleHeight == null ? defaultHeight = '320' : defaultHeight = bundleHeight;
    bundleUiColor == null ? defaultUiColor = 'FFFFFF' : defaultUiColor = bundleUiColor;
    bundleUiBgColor == null ? defaultUiBgColor = '000000' : defaultUiBgColor = bundleUiBgColor;
    bundleBgColor == null ? defaultBgColor = '000000' : defaultBgColor = bundleBgColor;
    var width;
    var height;
    var buffer;
    var uiColor;
    var uiBgColor;
    var bgColor;
    var loop;
    var auto;
    var showDuration;
    var fileUrl;

    var content = editor.getSelection().getSelectedElement();
    if (content) {

        var object = content.getAttribute('data-cke-realelement');
        object = decodeURIComponent(object);


        var widthMatch = object.match(/width="([0-9]+)"/);
        if (widthMatch instanceof Array) {
            width = widthMatch[1];
        }

        var heightMatch = object.match(/height="([0-9]+)"/);
        if (heightMatch instanceof Array) {
            height = heightMatch[1];
        }

        var bufferMatch = object.match(/&amp;buffer=([^&]+)/);
        if (bufferMatch instanceof Array) {
            buffer = bufferMatch[1];
        }

        var uiColorMatch = object.match(/&amp;controlColor=0x([^&]+)/);
        if (uiColorMatch instanceof Array) {
            uiColor = uiColorMatch[1];
        }

        var uiBgColorMatch = object.match(/&amp;controlBackColor=0x([^&]+)/);
        if (uiBgColorMatch instanceof Array) {
            uiBgColor = uiBgColorMatch[1];
        }

        var bgColorMatch = object.match(/&amp;playerbackcolor=0x([^&]+)/);
        if (bgColorMatch instanceof Array) {
            bgColor = bgColorMatch[1];
        }

        var loopMatch = object.match(/&amp;loop=([^&]+)/);
        if (loopMatch instanceof Array) {
            loop = loopMatch[1];
        }

        var autoplayMatch = object.match(/&amp;autoPlay=([^&]+)/);
        if (autoplayMatch instanceof Array) {
            auto = autoplayMatch[1];
        }

        var durationMatch = object.match(/&amp;showduration=([^&]{4,5})"/);
        if (durationMatch instanceof Array) {
            showDuration = durationMatch[1];
        }

        var fileMatch = object.match(/mediaURL=([^&]+)/);
        if (fileMatch instanceof Array) {
            fileUrl = fileMatch[1];
        }

    }

    function GetFlashObjectDiv() {

        fileUrl = CKEDITOR.dialog.getCurrent().getContentElement('info', 'video_url').getValue();
        width = CKEDITOR.dialog.getCurrent().getContentElement('info', 'width').getValue();
        height = CKEDITOR.dialog.getCurrent().getContentElement('info', 'height').getValue();
        buffer = CKEDITOR.dialog.getCurrent().getContentElement('info', 'buffer').getValue();
        auto = CKEDITOR.dialog.getCurrent().getContentElement('info', 'auto').getValue();
        uiColor = CKEDITOR.dialog.getCurrent().getContentElement('info', 'uiColor').getValue();
        uiBgColor = CKEDITOR.dialog.getCurrent().getContentElement('info', 'uiBgColor').getValue();
        bgColor = CKEDITOR.dialog.getCurrent().getContentElement('info', 'bgColor').getValue();
        loop = CKEDITOR.dialog.getCurrent().getContentElement('info', 'loop').getValue();
        showDuration = CKEDITOR.dialog.getCurrent().getContentElement('info', 'showDuration').getValue();


        if (width == '') {
            width = '480';
        }
        if (height == '') {
            height = '320';
        }
        if (uiColor == '') {
            uiColor = 'FFFFFF';
        }
        if (bgColor == '') {
            bgColor = '000000';
        }

        var flashObjectDiv;
        flashObjectDiv = '<object type="application/x-shockwave-flash" ';
        flashObjectDiv += 'id="video" ';
        flashObjectDiv += 'name="video" ';
        flashObjectDiv += 'bgcolor="#000000" ';
        flashObjectDiv += 'data="' + player + '" ';
        flashObjectDiv += 'width="' + width + '" height="' + height + '">';
        flashObjectDiv += '<param name="movie" value="' + player + '">';
        flashObjectDiv += '<param name="menu" value="false">';
        flashObjectDiv += '<param name="allowFullScreen" value="true">';
        flashObjectDiv += '<param name="allowScriptAccess" value="always">';
        flashObjectDiv += '<param name="flashvars" value="mediaURL=' + fileUrl + '&amp;';
        flashObjectDiv += 'autoPlay=' + auto + '&amp;';
        flashObjectDiv += 'buffer=' + buffer + '&amp;';
        flashObjectDiv += 'showTimecode=true&amp;';
        flashObjectDiv += 'loop=' + loop + '&amp;';
        flashObjectDiv += 'controlColor=0x' + uiColor + '&amp;';
        flashObjectDiv += 'controlBackColor=0x' + uiBgColor + '&amp;';
        flashObjectDiv += 'playerbackcolor=0x' + bgColor + '&amp;';
        flashObjectDiv += 'scaleIfFullScreen=true&amp;';
        flashObjectDiv += 'showScalingButton=true&amp;';
        flashObjectDiv += 'defaultVolume=100&amp;';
        flashObjectDiv += 'showduration=' + showDuration + ' ">';
        flashObjectDiv += '<param name="wmode" value="opaque">';
        flashObjectDiv += '</object>';

        return flashObjectDiv;
    }

    return {

        title: editor.lang.video.title,
        minWidth: 200,
        minHeight: 240,
        contents: [
            {
                id: 'info',
                elements: [
                    {
                        type: 'vbox',
                        children: [
                            {
                                type: 'hbox',
                                align: 'left',
                                children: [
                                    {
                                        type: 'text',
                                        id: 'video_url',
                                        style: 'width:380px',
                                        label: editor.lang.video.video_url,
                                        default: fileUrl
                                    },
                                    {
                                        type: 'button',
                                        id: 'browse',
                                        filebrowser: {
                                            action: 'Browse',
                                            target: 'info:video_url',
                                            url: buildConfig.files_path + '?mode=fileBrowser&type=file&returnTo=ckeditor&section='+CKEDITOR.video_section
                                        },
                                        label: editor.lang.common.browseServer,
                                        style: 'display:inline-block;margin-top:8px;'
                                    }
                                ]
                            },
                        ]
                    },
                    {
                        type: 'hbox',
                        align: 'left',
                        children: [
                            {
                                type: 'text',
                                id: 'width',
                                'maxLength': 4,
                                'default': width,
                                label: editor.lang.video.width
                            },
                            {
                                type: 'text',
                                id: 'height',
                                'maxLength': 4,
                                'default': height,
                                label: editor.lang.video.height
                            },
                            {
                                type: 'text',
                                id: 'buffer',
                                'maxLength': 1,
                                'default': buffer,
                                label: editor.lang.video.buffer
                            }
                        ]
                    },
                    {
                        type: 'hbox',
                        align: 'left',
                        children: [
                            {
                                type: 'text',
                                id: 'uiColor',
                                'maxLength': 7,
                                'default': uiColor,
                                label: editor.lang.video.ui_color
                            },
                            {
                                type: 'text',
                                id: 'uiBgColor',
                                'maxLength': 7,
                                'default': uiBgColor,
                                label: editor.lang.video.ui_bg_color
                            },
                            {
                                type: 'text',
                                id: 'bgColor',
                                'maxLength': 7,
                                'default': bgColor,
                                label: editor.lang.video.bg_color
                            }
                        ]
                    },
                    {
                        type: 'hbox',
                        align: 'left',
                        style: 'width:100px',
                        children: [
                            {
                                type: 'checkbox',
                                id: 'loop',
                                'default': loop,
                                label: editor.lang.video.loop
                            },
                            {
                                type: 'checkbox',
                                id: 'auto',
                                'default': auto,
                                label: editor.lang.flash.chkPlay
                            },
                            {
                                type: 'checkbox',
                                id: 'showDuration',
                                'default': showDuration,
                                label: editor.lang.video.duration
                            }
                        ]
                    }
                ]
            }
        ],
        buttons: [CKEDITOR.dialog.okButton, CKEDITOR.dialog.cancelButton],

        onOk: function () {
            editor.insertHtml(GetFlashObjectDiv());
        }
    }
});
