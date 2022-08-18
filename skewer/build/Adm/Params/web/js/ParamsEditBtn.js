/**
 * Настраиваемая кнопка для строк редактировать/исправить для раздела
 */
Ext.define('Ext.Adm.ParamsEditBtn', {
    extend: 'Ext.Component',
    state: 'edit_form',
    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {
        var me = this.items[1];

        var sectionId = processManager.getMainContainer(grid).serviceData.sectionId;
        if (rec.get('parent') == sectionId) {
            // редактировать
            me.tooltip = me.lang.upd;
            return 'icon-edit';
        } else {
            // Исправить для раздела
            me.tooltip = me.lang.paramAddForSection;
            return 'icon-add';
        }
    },
    handler: function(grid, rowIndex) {
        var mainContainer = processManager.getMainContainer(grid);
        var rec = grid.getStore().getAt(rowIndex);
        // редактировать
        processManager.setData(mainContainer.path,Ext.merge({
            cmd: 'edit',
            data: {id: rec.get('id')}
        },mainContainer.serviceData));
        // отправить запрос
        processManager.postData();
    }
});
