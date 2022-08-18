Ext.define('Ext.Tool.ApproveBtn', {
    extend: 'Ext.Component',

    New: 0,
    Active: 1,
    NoActive: 2,

    getClass: function(value, meta, rec) {

        var actCol = this.items[0];

        switch ( parseInt(rec.get('status')) ) {
            case actCol.New:
            case actCol.NoActive:
                return 'icon-saved';
            default:
                return '';
        }
    },

    handler: function(grid, rowIndex) {
        var actCol = this.items[0];
        var new_status;
        var rec = grid.getStore().getAt(rowIndex);
        var mainContainer = processManager.getMainContainer(grid);

        var status = parseInt(rec.get('status'));

        new_status = actCol.Active;

        var data = rec.data;
        data.status = new_status;

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'changeStatus',
            data: data
        },mainContainer.serviceData));
        processManager.postData();

    }
});