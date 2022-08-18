/**
 * Корневой слой вывода интерфейса
 */

Ext.define('Ext.Cms.Layout',{
    extend: 'Ext.Viewport',
    id: 'view-port',
    layout: 'border',
    margin: 0,
    padding: 5,
    items: [{
        region: 'center',
        html: ''
    }],

    // после инициализации объекта
    afterRenderInterface:function(){

        if ( pageHistory )
            pageHistory.afterRender();

    }

});
