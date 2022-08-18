CKEDITOR.plugins.add('tooltip', {
    requires: 'widget',
    icons: 'tooltip',
    lang: ['ru','en'],
    init: function(editor) {
        editor.widgets.add('tooltip', {
            button: editor.lang.tooltip.popup_text,
            template: '<span class="b-tooltip"></span>',
            dialog: 'tooltipDialog',
            command: 'tooltip',
            toolbar: 'insert',
            allowedContent: 'span(!b-tooltip){tooltip_id}',
            upcast: function(element) {
                return element.name == 'span' && element.hasClass('b-tooltip');
            },
            init: function() {
                if (this.element.getAttribute('tooltip_id'))
                    this.setData('tooltip_id', this.element.getAttribute('tooltip_id'));
                else
                    this.setData('tooltip_id','0');

            },
            data: function() {
                if ( this.data.tooltip_id == '' )
                    this.element.removeAttribute( 'tooltip_id' );
                else
                    this.element.setAttribute( 'tooltip_id', this.data.tooltip_id );

                if (typeof editor.tooltip_content != 'undefined' && editor.tooltip_content != ''){
                    this.element.setHtml(editor.tooltip_content);
                    editor.tooltip_content = '';
                }
            }
        });
        CKEDITOR.dialog.add('tooltipDialog', this.path + 'dialogs/tooltip.js');
        editor.ui.addButton( 'tooltip', {
            label: editor.lang.tooltip.popup_text,
            command: 'tooltip',
            toolbar: 'insert'
        });
    }
});