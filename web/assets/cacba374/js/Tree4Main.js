/**
 * Форма для создания и редактирования разделов
 */
Ext.define('Ext.Adm.Tree4Main', {
    extend: 'Ext.Adm.Tree',
    cls: 'sk-tree',

    viewConfig: {
        plugins: [{
            ddGroup: 'ddMain',
            ptype  : 'treeviewdragdrop'
        }],
        listeners: {
            beforedrop: function(node, data, overModel, dropPosition){
                this.up('panel').onBeforeDrop( node, data, overModel, dropPosition );
            }
        }
    },

    addDockedButtons: function(){

        var me = this;

        this.callParent();
        if ( this.showSettings ) {
            this.tbar.push({
                text: me.lang.siteSettings,
                iconCls: 'icon-edit',
                scope: this,
                handler: this.showSiteSettings
            });
        }

    },

    // вызов основных настроек сайта
    showSiteSettings: function(){

        var me = this;

        if ( this.useHistory ) {

            this.nowSectionId = me.sectionId;
            me.sectionId = me.rootSection;

            // изменить контрольную точку страницы
            processManager.fireEvent('location_change');

        } else {

            // иначе просто перейти к разделу
            this.findSection( me.rootSection );

        }

    }

});
