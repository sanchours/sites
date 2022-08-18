/**
 * Настраиваемая кнопка для удаления полей карточки товара
 */
Ext.define('Ext.Catalog.DelFieldBtn', {
    extend: 'Ext.Component',
    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {
        if (rec.data.prohib_del != 1)
            return 'icon-delete';

    },
    handler: function(grid, rowIndex) {
        var mainContainer = processManager.getMainContainer(grid);
        var rec = grid.getStore().getAt(rowIndex);
        if (rec.data.prohib_del != 1) {
            // удалить
            var row_text = rec.get('title');

            if ( !row_text )
                row_text = rec.get('name');
            var text = sk.dict('delRow').replace('{0}', row_text);
            Ext.MessageBox.confirm(sk.dict('delRowHeader'), text, function(res){
                if ( res !== 'yes' ) return;

                // собрать посылку
                processManager.setData(mainContainer.path,Ext.merge({
                    cmd: 'FieldRemove',
                    data: {id: rec.get('id')}
                },mainContainer.serviceData));

                // отправить запрос
                processManager.postData();

            });

        }
    }
});
