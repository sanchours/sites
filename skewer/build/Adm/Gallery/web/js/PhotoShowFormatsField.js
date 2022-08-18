/**
 * Библиотека для отображения набора изображения в виде поля
 */
Ext.define('Ext.Adm.PhotoShowFormatsField',{
    extend: 'Ext.tab.Panel',
    plain: true,
    border: 0,
    defaults: {
        border: 0
    },
    height: 370,
    items: [],
    activeTab: 0, // Индекс активного таба при показе компонента

    initComponent: function(){

        var me = this,
            itemId, item
        ;

        me.items = [];

        // перебор пришедших элементов
        for ( itemId in me.value ) {

            // инициализационный макссив
            item = me.value[itemId];

            // создаем подчиненный элемент
            item.items = Ext.create('Ext.Adm.PhotoImg', {
                renderData: item
            });

            // добавляем в набор элементов
            me.items.push( item );

        }

        // отключаем заголовок у панели
        me.title = '';

        me.callParent();

//    },
//
//    onDestroy: function(){
//
//        alert('dest');
//
//        this.removeAll();

    },

    isFormField: true,

    isDirty: function(){
        return false;
    },

    isValid: function(){
        return true;
    },

    getSubmitData: function() {
        return { selectedFormat: this.getActiveTab().name };

    }


});
