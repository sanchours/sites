/**
 * Библиотека для отображения списка фотографий галереи
 */

Ext.define('Ext.Adm.FileBrowserImages',{

    extend: 'Ext.panel.Panel',

    height: '100%',
    width: '100%',
    border: 0,
    autoScroll : true,

    viewPanel: null,
    items: [],

    initComponent: function() {

        var me = this;

        me.items = [
            me.viewPanel = Ext.create('Ext.Adm.FileImageListView'),
            lang = me.lang
        ];

        me.callParent();

    },

    /**
     * Выполняется после рендеринга объекта
     * first open hack
     */
    afterProcessSet: function() {
        var me = this;
        var body = Ext.get( me.id+'-body' );
        if ( body )
            body.setHeight('100%');
    },

    execute: function( data, cmd ) {

        var me = this;
        var selection,
            value, key
        ;

        switch ( cmd ) {

            // отобразить список файлов
            case 'load_list':

                // загрузить файлы
                me.viewPanel.getStore().loadData(data.files);

                // подсветка новых
                var loadedFiles = data['loadedFiles'] || [];
                if ( loadedFiles.length ) {
                    for ( key in data.files ) {
                        var item = data.files[key];
                        if ( Ext.Array.contains( loadedFiles, item['name']) ) {
                            me.viewPanel.getSelectionModel().select(parseInt(key));
                        }
                    }
                }

                break;

            // выбрать файл
            case 'selectFile':

                // взять выбранную строку
                selection = me.viewPanel.getSelectionModel().getSelection();

                // если не выбрано - выдать ошибку
                if ( !selection.length ) {
                    sk.error(me.lang.fileBrowserNoSelection);
                    break;
                }

                // выполнить переданные директивы
                value = selection[0].get('webPathShort');
                processManager.fireEvent( 'select_file_set', value );

                break;

            // удаление элемента
            case 'delete':

                // взять выбранную строку
                selection = me.viewPanel.getSelectionModel().getSelection();

                // если не выбрано - выдать ошибку
                if ( !selection.length ) {
                    sk.error(me.lang.fileBrowserNoSelection);
                    break;
                }

                // задание текста для подтверждения
                var row_text;
                if ( selection.length === 1 ) {
                    row_text = selection[0].data['name'] || '';
                    if ( row_text ) {
                        row_text = '"'+row_text+'"';
                    } else {
                        row_text = me.lang.delRowNoName;
                    }
                } else {
                    row_text = selection.length.toString()+me.lang.delCntItems;
                }

                Ext.MessageBox.confirm(sk.dict('delRowHeader'), sk.dict('delRow').replace('{0}', row_text), function(res){
                    if ( res !== 'yes' ) return false;

                    // сборка параметров на удаление
                    var delItems = [];
                    for ( var row in selection ) {
                        delItems.push( selection[row].get('name') );
                    }

                    // собрать посылку на удаление
                    var cont = processManager.getMainContainer(me),
                        dataPack = cont.serviceData || {},
                        componentData = {
                            cmd: 'delete',
                            delItems: delItems
                        }
                    ;
                    dataPack = Ext.merge( dataPack, componentData );

                    processManager.setData(cont.path, dataPack);
                    processManager.postData();

                    return true;

                } );

                break;

                case 'copy_filelink':

                    selection = me.viewPanel.getSelectionModel().getSelection();

                    var row_text = '';
                    if (!selection.length) {
                        sk.error(me.lang.chooseFile);
                        return false;
                    }

                    selection.forEach(function(item, i) {
                        row_text += selection[i].data['webPathShort']+'<br>';
                    });

                    Ext.MessageBox.show({
                        title: me.lang.showFilesLink,
                        msg: row_text,
                        buttons: Ext.MessageBox.OK
                    });

                    break;
        }


    }


});
