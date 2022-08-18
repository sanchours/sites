Ext.define('Ext.Tool.TranslateBtn', {
    extend: 'Ext.Component',

    statusNotTranslated: 0,
    statusTranslated: 1,
    statusInProcess: 2,

    getClass: function(value, meta, rec) {

        var actCol = this.items[0];

        switch ( parseInt(rec.get('status')) ) {
            case actCol.statusNotTranslated:
                return 'icon-visible';
            default :
                return '';
        }

    },

    handler: function(grid, rowIndex) {

        var actCol = this.items[0],
            rec,
            status,
            data
        ;

        rec = grid.getStore().getAt(rowIndex);
        status = parseInt(rec.get('status'));

        var translate = false;

        switch ( status ) {
            case actCol.statusNotTranslated:
                translate = true;
                break;
            default :
                break;
        }

        if ( translate ) {

            data = rec.data;

            var mainContainer = processManager.getMainContainer(grid);

            processManager.setData(mainContainer.path, Ext.merge({
                cmd: 'translate',
                data: data
            }, mainContainer.serviceData));
            processManager.postData();

        }

    }
});