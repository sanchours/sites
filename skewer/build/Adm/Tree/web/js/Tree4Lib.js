/**
 * Форма для создания и редактирования библиотек
 */
Ext.define('Ext.Adm.Tree4Lib', {
    extend: 'Ext.Adm.Tree',
    cls: 'sk-treeLib',

    addToRootNode: true,
    defaultSectionType: 1,

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
