/**
 * Библиотека для вывода раскладки файловго менеджера
 * Используется во всплывающем окне показа галерейного альбома
 */
Ext.define('Ext.Cms.SliderBrowser', {
    extend: 'Ext.Viewport',
    title: 'slider',
    height: '90%',
    width: '90%',
    layout: 'border',
    closeAction: 'hide',
    modal: true,
    componentsInited: false,

    senderData: {},

    defaults: {
        margin: '3 3 3 3'
    },

    defaultSection: 1,

    inited: false,

    items: [{
        region: 'center',
        html: 'viewport'
    }],

    showData: function( text ){
        sk.error( text );
    },

    execute: function( data, cmd ) {

        switch ( cmd ) {

            case 'init':

                // нужна перовичная перезагрузка иначе не успевают отстроиться высоты элементов корректно
                if ( !this.inited ) {
                    processManager.setData(this.path, {
                        cmd: 'init'
                    });
                    processManager.postData();
                    this.inited = true;
                }

                break;

        }

        if ( data.error )
            sk.error( data.error );
    }

});
