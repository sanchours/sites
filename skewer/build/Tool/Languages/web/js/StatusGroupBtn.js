Ext.define('Ext.Tool.StatusGroupBtn', {
    extend: 'Ext.Component',

    statusNotTranslated: 0,
    statusTranslated: 1,
    statusInProcess: 2,

    getClass: function(value, meta, rec) {

        var actCol = this.items[0];

        switch ( parseInt(rec.get('status')) ) {
            case actCol.statusNotTranslated:
            default :
                return 'icon-stop';
            case actCol.statusTranslated:
                return 'icon-saved';
            case actCol.statusInProcess:
                return 'icon-edit';
        }

    },

    handler: function(grid, rowIndex) {

        var actCol = this.items[0],
            rec,
            status,
            data,
            new_status
        ;

        rec = grid.getStore().getAt(rowIndex);
        status = parseInt(rec.get('status'));

        /**
         * не переведен -> переведен
         * переведен -> в процессе
         * в процессе -> переведен
         *
         * в статус не перевед не возвращаемя, т.к. за перевод уже взялись
         * единственный способ перейти в "не переведен" - удалить параметр
         */
        switch ( status ) {
            case actCol.statusNotTranslated:
            default :
                new_status = actCol.statusTranslated;
                break;
            case actCol.statusTranslated:
                new_status = actCol.statusInProcess;
                break;
            case actCol.statusInProcess:
                new_status = actCol.statusTranslated;
                break;
        }

        //grid.up('panel').execute( {}, 'loadItem' );
        data = rec.data;
        data.status = new_status;

        var mainContainer = processManager.getMainContainer(grid);

        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'save',
            data: data
        },mainContainer.serviceData));
        processManager.postData();

    }
});