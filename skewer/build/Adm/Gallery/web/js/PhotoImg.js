/**
 * Библиотека для отображения картинки в заданном формате
 */
Ext.define('Ext.Adm.PhotoImg',{

    extend: 'Ext.AbstractComponent',
    border: 0,
    padding: 5,
    renderData: {},
    value: false,
    imgMaxHeight: 300,
    renderTpl: '<p>{title}: {width}x{height}</p><img src="{src}" alt="{title}" {addAttr}>',

    initComponent: function(){

        var me=this,
            height
        ;

        // если есть спец параметр - заменить стандартный
        if ( me.value )
            me.renderData = me.value;

        // проверка ниличия обязательного параметра
        if ( !me.renderData.src )
            throw 'Ext.Adm.PhotoImg: no src param passed.';

        // заданная высота
        height = me.renderData.height || 0;

        // ограничение по высоте
        me.renderData.addAttr = (height && height>me.imgMaxHeight) ? 'height="'+me.imgMaxHeight+'px;"' : '';

        //добавил для борьбы с кэшем
        me.renderData.src = me.renderData.src + '?v=' + (new Date().getTime());

        me.callParent();
    }
});
