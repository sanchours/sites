/**
 * Настраиваемая кнопка для возможности редактирования полей карточки товара
 */
Ext.define('Ext.Catalog.EditFieldBtn', {
    extend: 'Ext.Component',
    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {
        if (rec.data.no_edit != 1)
            return 'icon-edit';

    },
    handler: function(grid, rowIndex) {
            var mainContainer = processManager.getMainContainer(grid);
            var rec = grid.getStore().getAt(rowIndex);
            if (rec.get('no_edit') != 1) {
                // редактировать
                processManager.setData(mainContainer.path,Ext.merge({
                    cmd: 'FieldEdit',
                    data: {id: rec.get('id')}
                },mainContainer.serviceData));
                // отправить запрос
                processManager.postData();
            }
    }
});
