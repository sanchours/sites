/**
 * Система отображения списка площадок
 */

// модель данных площадок
Ext.define('ServiceListModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'string' },
        {name: 'group', type: 'string'},
        {name: 'title', type: 'string'},
    ]
});

// хранилище для площадок
Ext.define('ServiceStore', {
    extend: 'Ext.data.Store',
    groupField: 'group',
    model: 'ServiceListModel',
    sorters: ['title']
});


Ext.define('Ext.Tool.LeftList', {
    extend: 'Ext.panel.Panel',
    title: 'tool',
    cls:'sk-control-panel',
    store: null,
    grid: null,
    autoScroll: true,
    split: true,
    height: 100,
    width: 275,
    minWidth: 275,
    maxWidth: 400,
    collapsible: true,
    animCollapse: true,
    margins: '0 0 0 0',
    region: 'west',
    useHistory: true,
    layerName: 'Tool',

    // текущее значение выбранного элемента
    itemId: '',

    // id ставится при клике для использования в истории
    itemToSet: '',


    /**
     * Инициализация
     */
    initComponent: function() {

        // генерация объекта
        this.callParent();

        var container = this;

        container.store = Ext.create('ServiceStore', {
            cont: container
        });

        container.grid = Ext.create('Ext.Tool.LeftListGrid', {
            extend: 'Ext.grid.Panel',
            cont: container,
            store: container.store
        });
        container.add( container.grid );

        this.on('collapse', this.onDeactivate, this);
        processManager.addEventListener( 'location_render', this.path, 'processToken' );
        processManager.addEventListener( 'location_set_value',this.path,'setToken');
        processManager.addEventListener( 'get_module_name',this.path,'getModuleName');

    },

    // обработка пришедших запросов
    execute: function( data, cmd ) {

        switch ( cmd ) {

            // инициализация
            case 'init':

                // показать список
                this.store.loadData( data.items );
                this.doLayout();
                break;
        }
    },

    /**
     * При сворачивании в интерфейсе
     */
    onDeactivate: function() {
        // чтобы при разворачивании сам загрузился
        this.itemToSet = this.itemId;
        this.itemId = 0;
        return true;
    },

    /**
     * Выбор элемента
     */
    selectItem: function(moduleName){

        var newModule,oldModule,
            me = this
        ;

        newModule = moduleName;
        oldModule = me.itemId;

        if ( newModule !== oldModule ) {
            // если используется история
            if ( me.useHistory ) {

                // раздел для выбора
                me.itemToSet = newModule;

                // изменить контрольную точку страницы
                pageHistory.locationChange();

            } else {

                // иначе просто перейти к разделу
                processManager.fireEvent( 'tabs_load', newModule, this.path );

            }

        } else {

            processManager.fireEvent( 'tabs_reload', newModule, this.path );

        }

    },

    // обработка токена истории
    processToken: function( data ){

        var newId,oldId,
            me = this
            ;

        // идентификатор раздела
        newId = data[me.path];
        oldId = me.itemId;

        // проверки
        if ( !newId ) return;
        if ( newId === oldId )
            return;

        // выбранный раздел
        me.itemId = newId;

        if ( oldId == 0 )
            me.itemToSet = newId;

        if ( me.collapsed )
            me.expand();

        var index = me.store.getById( newId );
        if ( index ) {
            me.grid.getView().getSelectionModel().select( index );
            processManager.fireEvent( 'tabs_load', newId, me.path );
        }

    },

    // обработка добавления данных в токен истории страниц
    setToken: function(){

        var me = this;

        if ( this.collapsed ) return;

        // если установлен "следующий" - взять его
        var id = me.itemToSet ? me.itemToSet : me.itemId;
        me.itemToSet = 0;

        processManager.setData(this.path,id,'locPack');

    },

    /**
     * для события запроса имени текущего активного модуля
     * @return {String|undefined}
     */
    getModuleName: function() {
        var me = this;
        if ( !this.collapsed && me.itemId )
            return me.layerName+'_'+me.itemId;
        else
            return undefined;
    }


});
