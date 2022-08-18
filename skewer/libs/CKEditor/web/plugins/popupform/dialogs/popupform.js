/**
 * Created by na on 23.12.2016.
 */
CKEDITOR.dialog.add( 'popupformDialog', function( editor ) {
    return {
        title: editor.lang.popupform.title,
        minWidth: 400,
        minHeight: 160,
        contents: [
            {
                id: 'tab-basic',
                label: editor.lang.popupform.title,

                // The tab content.
                elements: [
                    {
                        type: 'text',
                        id: 'text',
                        label: editor.lang.popupform.text_caption,
                        validate: CKEDITOR.dialog.validate.notEmpty( editor.lang.popupform.error_text ),
                        default: 'Callback form'
                    },
                    {
                        type: 'select',
                        id: 'width_type',
                        className: 'width_type_'+editor.id,
                        label: editor.lang.popupform.width_type,
                        items: [['%'],['px']],
                        'default': '%'
                    },
                    {
                        type: 'text',
                        id: 'width',
                        label: editor.lang.popupform.width_caption,
                        validate: function(){

                            var width_type = document.querySelectorAll('div.width_type_'+editor.id)[0].getElementsByTagName('select')[0];

                            if (width_type.value=='%'){
                                if (this.getValue()<0 || this.getValue()>100) {
                                    alert(editor.lang.popupform.error_width);
                                    return false;
                                } else
                                    return true;
                            } else {
                                return true;
                            }
                        },
                        default: 60,
                    },
                    {
                        type: 'text',
                        id: 'section',
                        label: editor.lang.popupform.section_caption,
                        default: '0',
                        validate: CKEDITOR.dialog.validate.integer( editor.lang.popupform.error_section )
                    }
                ]
            }
        ],
        onOk: function() {
            var dialog = this;

            var popupform = editor.document.createElement( 'popupform' );

            var text = dialog.getValueOf( 'tab-basic', 'text' );
            var width = dialog.getValueOf( 'tab-basic', 'width' );
            var section = dialog.getValueOf( 'tab-basic', 'section' );
            var width_type = dialog.getValueOf( 'tab-basic', 'width_type' );

            var content = '<a class="js-callback" href="#" data-js_max_width="' + width + '" data-section="' + section + '" data-width-type="'+width_type+'">' + text + '</a>';

            popupform.setHtml( content );

            editor.insertElement( popupform );
        }
    };
});