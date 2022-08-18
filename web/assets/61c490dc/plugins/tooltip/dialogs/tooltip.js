/**
 * Created by na on 23.12.2016.
 */
CKEDITOR.dialog.add( 'tooltipDialog', function( editor ) {

    var html_block = '<div id="tooltip_iframe_div_'+editor.id+'" style="width:750px;height:600px"><iframe id="tooltip_iframe_'+editor.id+'" style="width:100%;min-width:750px;height:600px" width="750" height="600" src=""></iframe></div>';

    return {
        title: editor.lang.tooltip.title,
        minWidth: 750,
        minHeight: 500,
        contents: [
            {
                id: 'tab-basic',
                label: editor.lang.tooltip.popup,

                // The tab content.
                elements: [
                    {
                        type: 'html',
                        html: html_block,
                        id: 'content_block'
                    },
                    {
                        id: 'tooltip_id',
                        type: 'text',
                        className: 'tooltip_id cke_dialog_ui_checkbox_input tooltip_id_'+editor.id,
                        width: '50px',
                        setup: function( widget ) {
                            this.setValue( widget.data.tooltip_id );
                            var iframe = document.getElementById('tooltip_iframe_'+editor.id);
                            iframe.src = '/oldadmin/?mode=tooltipBrowser&tooltip_id='+ widget.data.tooltip_id;
                            
                        },
                        commit: function( widget ) {
                            widget.setData( 'tooltip_id', this.getValue() );
                        }
                    }
                ]
            }
        ],
        onShow: function(){
            document.querySelectorAll('div.tooltip_id_'+editor.id)[0].style.display = 'none';
            var content = getSelectionHtml(editor);
            if (content == ''){
                /*Ничего не выбрано. Прячем диалоги*/
                alert(editor.lang.tooltip.no_content);
                var closeBtns = document.getElementsByClassName('cke_dialog_close_button');

                for (var i = 0; i < closeBtns.length; i++) {
                  //  console.log(closeBtns[i]);
                    closeBtns[i].click();
                }
            }
        },
        onOk: function(widget) {

            var checked_id = 0;

            var iframe = document.getElementById('tooltip_iframe_'+editor.id);

            var iframeDoc = iframe.contentWindow.document;
            var trs = iframeDoc.getElementsByClassName('x-grid-row');

            var tmp_value = document.querySelectorAll('div.tooltip_id_'+editor.id)[0].getElementsByTagName('input')[0].value;

            for (var i = 0; i < trs.length; i++) {
                var data = trs[i].innerHTML;
                if (data.indexOf('[+]')!='-1'){
                    var divs = trs[i].getElementsByClassName('x-grid-cell-inner');
                    checked_id = divs[0].innerText;
                    divs[1].innerText = '';
                }
            }

            if (checked_id!=0){
                //типа все нормально
                document.querySelectorAll('div.tooltip_id_'+editor.id)[0].getElementsByTagName('input')[0].value = checked_id;
                if (tmp_value==0) {
                    editor.tooltip_content = getSelectionHtml(editor);
                }
            } else {
                alert(editor.lang.tooltip.error_nothing_checked);
                return false;
            }
        }
    };
});

function getSelectionHtml(editor) {
    var sel = editor.getSelection();
    var ranges = sel.getRanges();
    var el = new CKEDITOR.dom.element("div");
    for (var i = 0, len = ranges.length; i < len; ++i) {
        el.append(ranges[i].cloneContents());
    }
    return el.getHtml();

}

function createElement(str)
{
    var div = document.createElement('div');
    div.innerHTML = str;
    return div.childNodes;
}