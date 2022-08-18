Ext.define('Ext.Tool.OverrideBtn', {
    extend: 'Ext.Component',

    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {

        if (parseInt(rec.data.override)) {
            return 'icon-reload';
        }
        else {
            return '';
        }
    },
    handler: function(grid, rowIndex) {

        var rec = grid.getStore().getAt(rowIndex);
        var mainContainer = processManager.getMainContainer(grid);

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'unsetOverride',
            data: {language: rec.get('language'), category: rec.get('category'), message: rec.get('message')}
        },mainContainer.serviceData));
        processManager.postData();
    }
});