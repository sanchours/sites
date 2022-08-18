(function () {
    CKEDITOR.video = {};
    CKEDITOR.video.playbutton = '';
    CKEDITOR.video.width = 480;
    CKEDITOR.video.height = 320;
    CKEDITOR.video.uiColor = '#FFFFFF';
    CKEDITOR.video.uiBgColor = '#FFFFFF';
    CKEDITOR.video.bgColor = '#000000';
    CKEDITOR.plugins.add( 'video', {

        lang: ['ru', 'en'],
        init: function( editor ) {

            CKEDITOR.tools.extend(CKEDITOR, {
                videoPath: '%_CKEDITOR_WEB_DIR_%/plugins/video/'
            });
            var pluginName='video';
            CKEDITOR.dialog.add('swfobject',this.path+'dialogs/swfobject.js');
            CKEDITOR.dialog.add(pluginName,this.path+'dialogs/video.js');

            editor.addCommand(pluginName,new CKEDITOR.dialogCommand(pluginName));
            editor.ui.addButton( 'video', {
                label: editor.lang.video.title,
                command: 'video',
                icon: this.path+'player.png'
            });
        }
        
    });
})();
