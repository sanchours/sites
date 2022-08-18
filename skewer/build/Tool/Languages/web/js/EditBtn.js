Ext.define('Ext.Tool.EditBtn', {
    extend: 'Ext.Component',

    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {

        return 'icon-edit';
    },
    handler: function(grid, rowIndex) {

        var rec = grid.getStore().getAt(rowIndex);
        var mainContainer = processManager.getMainContainer(grid);

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'show',
            data: {language: rec.get('language'), category: rec.get('category'), message: rec.get('message')}
        },mainContainer.serviceData));
        processManager.postData();
    }
});