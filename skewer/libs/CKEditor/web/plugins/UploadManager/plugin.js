/*
 * @example An iframe-based dialog with custom button handling logics.
 */
(function () {
    CKEDITOR.plugins.add('UploadManager',
        {
            requires: [ 'iframedialog' ],
            init: function (editor) {

                var me = this;
                CKEDITOR.dialog.add('UploadManagerDialog', function (instance) {
                    return {
                        title: 'Upload Manager',
                        minWidth: 550,
                        minHeight: 200,
                        contents: [
                            {
                                id: 'iframe',
                                label: 'Upload Manager',
                                expand: true,
                                elements: [
                                    {
                                        type: 'html',
                                        id: 'pageUploadManager',
                                        label: 'Upload Manager',
                                        style: 'width : 800px;',
                                        html: '<iframe src="' + me.path + '/dialogs/UploadManager.html" frameborder="0" name="iframeUploadManager" id="iframeUploadManager" allowtransparency="1" style="width:800px;margin:0;padding:0;height: 500px;"></iframe>'
                                    }
                                ]
                            }
                        ],
                        onShow: function() {
                          $("#iframeUploadManager").contents().find('#img-list').html("");
                        },
                        
                        onOk: function () {
                        
//                             for (var i = 0; i < window.frames.length; i++) {
// 
//                                 if (window.frames[i].name == 'iframeUploadManager') {
//                                     var content = window.frames[i].document.getElementById("embed").value;
//                                 }
//                             }
//                             var final_html = 'UploadManagerInsertData|---' + escape('<div class="media_embed">' + content + '</div>') + '---|UploadManagerInsertData';
//                             instance.insertHtml(final_html);
//                             var updated_editor_data = instance.getData();
//                             var clean_editor_data = updated_editor_data.replace(final_html, '<div class="media_embed">' + content + '</div>');
//                             instance.setData(clean_editor_data);
                        }
                    };
                });

                editor.addCommand('UploadManager', new CKEDITOR.dialogCommand('UploadManagerDialog'));

                editor.ui.addButton('UploadManager',
                    {
                        label: 'Массовый загрузчик изображений',
                        command: 'UploadManager',
                        icon: this.path + 'images/icon.gif'
                    });
            }
        });
})();
