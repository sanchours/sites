/**
 * Форма для создания и редактирования шаблонов
 */
Ext.define('Ext.Adm.Tree4Tpl', {
    extend: 'Ext.Adm.Tree',
    cls: 'sk-treeTpl',

    addToRootNode: true,

    viewConfig: {
        plugins: [{
            ddGroup: 'ddLib',
            ptype  : 'treeviewdragdrop'
        }],
        listeners: {
            beforedrop: function(node, data, overModel, dropPosition){
                this.up('panel').onBeforeDrop( node, data, overModel, dropPosition );
            }
        }
    }

});
