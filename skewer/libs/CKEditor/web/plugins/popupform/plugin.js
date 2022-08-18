CKEDITOR.plugins.add( 'popupform', {
    icons: 'popupform',
    lang: ['en','ru','de'],

    init: function( editor ) {
        editor.addCommand( 'popupform', new CKEDITOR.dialogCommand( 'popupformDialog' ) );
        editor.ui.addButton( 'popupform', {
            label: editor.lang.popupform.title,
            command: 'popupform',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'popupformDialog', this.path + 'dialogs/popupform.js' );
    }
});