//noinspection JSUnusedLocalSymbols
/**
 * Настраиваемая кнопка для очистки перекрытого значения
 */
Ext.define('Ext.Tool.ParamsCleanObjBtn', {
    extend: 'Ext.Component',
    state: 'edit_form',


    getClass: function (value, meta, rec, rowIndex, colIndex, store, grid) {

        var me = this.items[1];

        if (rec.get('defaultValueReplaced') === true) {
            return 'icon-broom';
        } else {
            me.tooltip = '';
            return '';
        }

    },
    handler: function (grid, rowIndex, colIndex, item) {
        var rootCont = processManager.getMainContainer(grid);
        var rec = grid.getStore().getAt(rowIndex);
        var addParams = item.addParams || {};

        if (rec.get('defaultValueReplaced') === true) {

            Ext.MessageBox.confirm(sk.dict('allowDoHeader'), item.actionText, function (res) {
                if (res !== 'yes') {
                    return false;
                }

                // данные к отправке
                var dataPack = {};
                Ext.merge(
                    dataPack,
                    rootCont.serviceData,
                    addParams,
                    {
                        from: 'list',
                        cmd: 'DeleteValueLabel',
                        data: rec.data
                    }
                );

                processManager.setData(rootCont.path, dataPack);

                rootCont.setLoading(true);

                if (item.doNotUseTimeout) {
                    processManager.doNotUseTimeout();
                }

                processManager.postData();

                return true;
            });

        }
    }

});
