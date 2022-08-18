Ext.define('Ext.Catalog.ViewModificationsBtn', {
    extend: 'Ext.Component',

    getClass: function(value, meta, rec) {
        if ( rec.get('modifications') )
            return 'icon-page';

        this.items[0].tooltip = '';
        return '';
    },

    handler: function(grid, rowIndex) {

        var rec = grid.getStore().getAt( rowIndex );
        var mainContainer = processManager.getMainContainer( grid );
        var data = rec.data;
         if ( rec.get('modifications') ) {
            processManager.setData(mainContainer.path,Ext.merge({
                cmd: 'modificationsItems',
                data: data
            },mainContainer.serviceData));
            processManager.postData();
        }

    }
});