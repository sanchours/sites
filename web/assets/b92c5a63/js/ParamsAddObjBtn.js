//noinspection JSUnusedLocalSymbols
/**
 * Настраиваемая кнопка для строк редактировать/исправить для раздела
 */
Ext.define('Ext.Adm.ParamsAddObjBtn', {
    extend: 'Ext.Component',
    state: 'edit_form',


    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {

        var me = this.items[0];

        if (rec.get('name') == 'object' || rec.get('name') == 'objectAdm') {
            // редактировать
            me.tooltip = me.lang.upd;
            return 'icon-configuration';
        } else {
            // Исправить для раздела
            me.tooltip = '';
            return '';
        }

    },
    handler: function(grid, rowIndex) {
        var mainContainer = processManager.getMainContainer(grid);
        var rec = grid.getStore().getAt(rowIndex);
        if (rec.get('name') == 'object' || rec.get('name') == 'objectAdm') {

            // редактировать
            processManager.setData(mainContainer.path,Ext.merge({
                cmd: 'addByTemplate',
                data: {id: rec.get('id'),type:rec.get('name'), parent:rec.get('parent')}
            },mainContainer.serviceData));
            // отправить запрос
            processManager.postData();

        } else {
        }
    }
});
