Ext.define('Ext.Adm.ApproveBtn', {
    extend: 'Ext.Component',

    useInZone: true,
    nonUseInZone: false,

    getClass: function(value, meta, rec) {
        var me = this.items[0];
        switch ( rec.get('useInZone') ) {
            case true:
                me.tooltip = me.lang.btnRow_disableModule;
                return 'icon-stop';
            case false:
                me.tooltip = me.lang.btnRow_enableModule;
                this.draggable = false;
                return 'icon-saved';
            default:
                return '';
        }
    },

    handler: function(grid, rowIndex) {
        var
            rec = grid.getStore().getAt(rowIndex),
            mainContainer = processManager.getMainContainer(grid),
            bUse = rec.get('useInZone'),
            data = rec.data;


        data.bUse = !bUse;

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'toogleActivity',
            data: data
        },mainContainer.serviceData));
        processManager.postData();

    }
});