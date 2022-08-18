/**
 * Левая панель для основного админского интерфейса
 */
Ext.define('Ext.Cms.LeftPanel',{
    extend: 'Ext.panel.Panel',
    region: 'west',
    title: '',//uildLang.leftPanelTitle,
    split: true,
    minWidth: 275,
    maxWidth: 400,
    collapsible: true,
    animCollapse: true,
    margins: '0 0 0 0',
    layout: 'accordion',
    items: [],

    initComponent: function() {

        var me = this,
            childPath, item
        ;

        me.title = me.lang.leftPanelTitle;

        processManager.addEventListener( 'location_render', this.path, 'processToken' );

        this.callParent();

        for ( childPath in me.initChildList ) {
            item = processManager.getProcess( me.initChildList[childPath] );
            if ( item ) {
                item.on('expand',function(){
                    // изменить контрольную точку страницы
                    processManager.fireEvent('location_change');
                });
            }
        }

    },

    execute: function( data ){

        this.processToken( pageHistory.getNowTokenData() );

        if ( data.error )
            sk.error( data.error );

    },

    processToken: function( data ){

        var me = this,
            childPath, item
        ;

        for ( childPath in data ) {
            if ( sk.inArray( childPath, me.initChildList ) ) {
                item = processManager.getProcess(childPath);
                item.expand();
                return true;
            }
        }

        return false;

    }

});
