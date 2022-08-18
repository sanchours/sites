// noinspection JSUnusedLocalSymbols
/**
 * Прототип для модуля
 *
 */
Ext.define('Ext.Builder.ShowFieldUpdater',{

    extend: 'Ext.AbstractComponent',

    path: '',
    store: null,
    grid: null,
    items: {},

    /**
     * Обработка пришедших событий
     * @param data данные
     * @param cmd команда
     */
    execute: function(data, cmd){
    },

    /**
     * пример функции до добавления. Если вернет false - поле не добавится
     * @param item
     */
    beforeAdd: function( item ) {},

    /**
     * пример функции
     * @param item
     */
    afterAdd: function( item ) {},

    /**
     * Отдает значение для заданного поля
     * @param name
     * @return string
     */
    getItemValue: function( name ) {
        if ( this.items[name] )
            return this.items[name];
        else
            return '';
    },

    /**
     * Задает массив значений
     * @param data
     */
    setItemValues: function( data ) {

        var key, item;

        for ( key in data ) {
            item = data[key];
            this.items[item.name] = item.value;
        }

    }

});
