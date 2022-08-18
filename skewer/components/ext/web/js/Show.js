/**
 * Автопостроитель интерфейсов
 * Класс для работы с текстовыми парами название-значение
 */

Ext.define('Ext.Builder.Show', {

    extend: 'Ext.grid.Panel',

    cls: 'sk-tab-show',

    height: '100%',
    width: '100%',
    hideHeaders: true,
    fieldUpdater: null,
    dropPanelDelimiter: true,
    flex: 1,

    ifaceData: {
        fieldUpdater: ''
    },

    columns: [{
        header: 'title',
        dataIndex: 'title',
        width: 150
    },{
        header: 'value',
        dataIndex: 'value',
        flex: 1
    }],

    listeners: {
        beforeselect: function(){
            return false;
        }
    },

    initComponent: function() {

        this.store = Ext.create('Ext.data.Store',{
            fields:[
                {name:'title', type:'string'},
                {name:'value', type:'string'}
            ],
            data: []
        });

        // создать объект
        this.callParent();

        if ( this.ifaceData.fieldUpdater ) {
            this.fieldUpdater = Ext.create('Ext.'+this.layerName+'.'+this.ifaceData.fieldUpdater,{
                grid: this,
                store: this.store,
                path: this.Builder.path
            });
        }

        // форма для полей
        var grid = this;
        var data = grid.ifaceData || [];
        var items = data.items || [];

        if ( items ) {

            var item;

            // удалить все поля
            grid.getStore().removeAll();

            // задать добор данных для преобразователя полей
            if ( this.fieldUpdater && this.fieldUpdater.setItemValues )
                this.fieldUpdater.setItemValues( items );

            // добавление полей
            for ( var fieldKey in items ) {

                item = items[fieldKey];

                // если есть метод выполнения до вставки поля
                if ( this.fieldUpdater && this.fieldUpdater.beforeAdd ) {
                    if ( this.fieldUpdater.beforeAdd(item) === false )
                        continue;
                }

                // описание для ExtJS
                var field = item;

                // добавить поле в форму
                grid.getStore().add( field );

                // если есть метод выполнения после вставки поля
                if ( this.fieldUpdater && this.fieldUpdater.afterAdd ) {
                    this.fieldUpdater.afterAdd(item);
                }

            }

        }


    },

    /**
     * Отдает строку с типом компонента list / show / form
     * @returns {string}
     */
    getType: function(){
        return 'show';
    },

    // обработка пришедших запросов
    execute: function( data, cmd ) {

        // запустить обработчик для преобразователя полей
        if ( this.fieldUpdater && this.fieldUpdater.execute )
            this.fieldUpdater.execute( data, cmd );

    }

});
