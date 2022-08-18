Ext.define('Ext.Adm.DeleteOrCloneBtn', {
    extend: 'Ext.Component',

    useInZone: true,
    nonUseInZone: false,

    getClass: function(value, meta, rec) {
        var me = this.items[1];
        switch ( rec.get('inherited') ) {
            case true:
                me.tooltip = me.lang.btnRow_copyParams;
                return 'icon-connect';
            case false:
                me.tooltip = me.lang.btnRow_deleteParams;
                this.draggable = false;
                return 'icon-delete';
            default:
                return '';
        }
    },

    handler: function(grid, rowIndex) {
        var
            rec = grid.getStore().getAt(rowIndex),
            mainContainer = processManager.getMainContainer(grid),
            // bInherited = rec.get('inherited'),
            data = rec.data;


        // data.inherited = !bInherited;

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'deleteOrCopy',
            data: data
        },mainContainer.serviceData));
        processManager.postData();

    }
});