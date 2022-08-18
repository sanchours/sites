/*
 * @example An iframe-based dialog with custom button handling logics.
 */
(function () {
    CKEDITOR.plugins.add('mediaembed',
        {
            requires: [ 'iframedialog' ],
            lang: ['ru', 'en'],
            icons: 'mediaembed', // %REMOVE_LINE_CORE%
            hidpi: true, // %REMOVE_LINE_CORE%
            init: function (editor) {

                var me = this;
                CKEDITOR.dialog.add('MediaEmbedDialog', function (instance) {
                    return {
                        title: editor.lang.mediaembed.title,
                        minWidth: 550,
                        minHeight: 200,
                        contents: [
                            {
                                id: 'iframe',
                                label: 'Embed Media',
                                expand: true,
                                elements: [
                                    {
                                        type: 'html',
                                        id: 'pageMediaEmbed',
                                        label: 'Embed Media',
                                        style: 'width : 100%;',
                                        html: '<iframe src="' + me.path + '/dialogs/mediaembed.html" frameborder="0" name="iframeMediaEmbed" id="iframeMediaEmbed" allowtransparency="1" style="width:100%;margin:0;padding:0;height: 200px;"></iframe>'
                                    }
                                ]
                            }
                        ],
                        onOk: function () {

                            for (var i = 0; i < window.frames.length; i++) {

                                if (window.frames[i].name == 'iframeMediaEmbed') {
                                    var content = window.frames[i].document.getElementById("embed").value;
                                }
                            }
                            var final_html = 'MediaEmbedInsertData|---' + escape('<div class="media_embed">' + content + '</div>') + '---|MediaEmbedInsertData';
                            instance.insertHtml(final_html);
                            var updated_editor_data = instance.getData();
                            var clean_editor_data = updated_editor_data.replace(final_html, '<div class="media_embed">' + content + '</div>');
                            instance.setData(clean_editor_data);
                        }
                    };
                });

                editor.addCommand('MediaEmbed', new CKEDITOR.dialogCommand('MediaEmbedDialog'));

                editor.ui.addButton('MediaEmbed',
                    {
                        label: editor.lang.mediaembed.hint,
                        command: 'MediaEmbed'
                        //icon: this.path + 'icon.gif'
                    });
            }
        });
})();
