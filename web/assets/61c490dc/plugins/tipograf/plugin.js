CKEDITOR.plugins.add('tipograf', {
    icons: 'tipograf',
    lang: ['en', 'ru', 'de'],

    init: function (editor) {
        editor.addCommand('tipograf', new CKEDITOR.dialogCommand('tipografDialog'));
        editor.ui.addButton('tipograf', {
            label: editor.lang.tipograf.title,
            command: 'tipograf',
        });

        CKEDITOR.dialog.add('tipografDialog', this.path + 'dialogs/tipograf.js');
    }
});