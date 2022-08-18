Ext.define('Ext.Tool.DelBtn', {
    extend: 'Ext.Component',

    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {

        return 'icon-delete';
    },
    handler: function(grid, rowIndex) {

        var rec = grid.getStore().getAt(rowIndex);
        var mainContainer = processManager.getMainContainer(grid);

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'delKey',
            data: {language: rec.get('language'), category: rec.get('category'), message: rec.get('message')}
        },mainContainer.serviceData));
        processManager.postData();
    }
});