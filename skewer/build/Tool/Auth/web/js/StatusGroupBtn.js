Ext.define('Ext.Tool.StatusGroupBtn', {
    extend: 'Ext.Component',

    statusNoAuth: 0,
    statusAuth: 1,
    statusBanned: 2,

    getClass: function(value, meta, rec) {

        var actCol = this.items[0];

        switch ( parseInt(rec.get('active')) ) {
            case actCol.statusNoAuth:
            default :
                return 'icon-upgrade';
            case actCol.statusAuth:
                return 'icon-saved';
            case actCol.statusBanned:
                return 'icon-stop';
        }

    },

    handler: function(grid, rowIndex) {
        var actCol = this.items[0];
        var new_status;
        var rec = grid.getStore().getAt(rowIndex);
        var mainContainer = processManager.getMainContainer(grid);

        var status = parseInt(rec.get('active'));

        switch ( status ) {
            case actCol.statusNoAuth:
            default :
                new_status = actCol.statusAuth;
                break;
            case actCol.statusAuth:
                new_status = actCol.statusBanned;
                break;
            case actCol.statusBanned:
                new_status = actCol.statusAuth;
                break;
        }

        var data = rec.data;
        data.active = new_status;

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'changeStatus',
            data: data
        },mainContainer.serviceData));
        processManager.postData();

    }
});