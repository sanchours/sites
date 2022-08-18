/**
 * Форма для создания и редактирования разделов
 */
Ext.define('Ext.Adm.Tree2FileBrowser', {
    extend: 'Ext.Adm.Tree',

    width: 200,
    minWidth: 200,
    resizable: false,
    margins: '6 -3 6 6',
    collapsible: false,

    useHistory: false,
    showButtons: false,
    eventPrefix: 'sectionFB',
    defaultSectionType: 1,

    initComponent: function() {

        this.columns = [ this.columns[0] ];

        this.callParent();

    }

});
