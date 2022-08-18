/**
 * Настраиваемая кнопка для строк редактировать/исправить для раздела
 */
Ext.define('Ext.Adm.ParamsDelBtn', {
    extend: 'Ext.Component',
    getClass: function(value, meta, rec,rowIndex,colIndex,store,grid) {
        var me = this.items[2];
        var sectionId = processManager.getMainContainer(grid).serviceData.sectionId;
        if (rec.get('parent') == sectionId) {
            // удалить
            me.tooltip = me.lang.del;
            return 'icon-delete';
        } else {
            // Дублировать для раздела
            me.tooltip = me.lang.paramCopyToSection;
            return 'icon-connect';
        }
    },
    handler: function(grid, rowIndex) {

        var mainContainer = processManager.getMainContainer(grid);
        var sectionId = mainContainer.serviceData.sectionId;
        var rec = grid.getStore().getAt(rowIndex);
        if (rec.get('parent') == sectionId) {
            // удалить
            var row_text = rec.get('title');
            if ( !row_text )
                row_text = rec.get('name');

            var text = sk.dict('delRow').replace('{0}', row_text);

            Ext.MessageBox.confirm(sk.dict('delRowHeader'), text, function(res){
                if ( res !== 'yes' ) return;

                // собрать посылку
                processManager.setData(mainContainer.path,Ext.merge({
                    cmd: 'delete',
                    data: {id: rec.get('id')}
                },mainContainer.serviceData));

                // отправить запрос
                processManager.postData();

            });

        } else {

            // Дублировать для раздела

            // собрать посылку
            processManager.setData(mainContainer.path,Ext.merge({
                cmd: 'clone',
                data: {id: rec.get('id')}
            },mainContainer.serviceData));

            // отправить запрос
            processManager.postData();

        }
    }
});
