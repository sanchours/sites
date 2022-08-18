/**
 * Расширение стандартного класса автогенериремых списков
 * под нужны выбор файлов во всплывающем окне
 */
// 23 - width row button delete
var windowWidth = '';

if (window.opener == null) { // Если открыто в общем интерфейсе
    windowWidth = '100%';
} else { //если открыто в новом окне
    windowWidth = Math.ceil( (100 - (23 * 100 / Ext.getBody().getViewSize().width)) ) + '%';
}

Ext.define('Ext.Adm.FileBrowserFiles',{

    extend: 'Ext.Builder.List',

    width: windowWidth,

    execute: function( data, cmd ){

        var me = this;

        switch ( cmd ) {

            case 'selectFile':

                // взять выбранную строку
                var selection = me.getView().getSelectionModel().getSelection();

                // если не выбрано - выдать ошибку
                if ( !selection.length ) {
                    sk.error(me.lang.fileBrowserNoSelection);
                    break;
                }

                // если выбрано больше одного файла,
                if (selection.length > 1) {
                    sk.message(me.lang.selectOneFile);
                    break;
                }

                // выполнить переданные директивы
                var value = selection[0].get('webPathShort');
                processManager.fireEvent( 'select_file_set', value );

                break;

            // удаление элемента
            case 'delete':

                // взять выбранную строку
                selection = me.getSelectionModel().selected;

                // если не выбрано - выдать ошибку
                if ( !selection.length ) {
                    sk.error(me.lang.fileBrowserNoSelection);
                    break;
                }

                // задание текста для подтверждения
                var row_text;
                if ( selection.length === 1 ) {
                    row_text = selection.items[0].data['name'] || '';
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
                    for ( var row in selection.items ) {
                        delItems.push( selection.items[row].get('name') );
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

                selection = me.getSelectionModel().selected;

                var row_text = '';
                if (!selection.length) {
                    sk.error(me.lang.chooseFile);
                    return false;
                }

                selection.items.forEach(function(item, i) {
                    row_text += item.data['webPathShort']+'<br>';
                });

                Ext.MessageBox.show({
                    title: me.lang.showFilesLink,
                    msg: row_text,
                    buttons: Ext.MessageBox.OK
                });

                break;

            default:
                this.callParent(arguments);
                break;

        }

    },

    /**
     * Событие по двойному клику на строке - выбрать запись
     */
    onDblClick: function() {

        this.execute( {}, 'selectFile' );

    }

});
