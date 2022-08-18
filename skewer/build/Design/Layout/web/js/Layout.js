/**
 * Корневой слой вывода интерфейса
 */

Ext.define('Ext.Design.Layout',{
    extend: 'Ext.Viewport',
    id: 'view-port',
    layout: 'border',
    items: [{
        region: 'center',
        html: 'viewport'
    }],

    // после инициализации объекта
    afterRenderInterface:function(){

        processManager.addEventListener('request_add_info', this.path, this.showError );

        if ( pageHistory )
            pageHistory.afterRender();

    },

    showError: function( text ) {
        sk.error( text );
    }

});
