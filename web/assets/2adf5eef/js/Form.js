/**
 * Автопостроитель интерфейсов
 * Класс для работы с формой
 */
Ext.define('Ext.Builder.Form', {
    extend: 'Ext.form.Panel',

    cls: 'sk-tab-form',

    height: '100%',
    width: '100%',
    flex: 1,
    autoScroll: true,
    border: 0,
    padding: 5,

    trackChanges : true,

    fieldDefaults: {
        labelAlign: 'left',
        margin: '2 5 2 0',
        labelWidth: 190,
        anchor: '100%'
    },

    items: [],

    initComponent: function() {

        this.trackChanges = this.ifaceData['trackChanges'];

        // сформировать набор полей
        this.items = Ext.create('Ext.sk.FieldTypes').createFields( this.ifaceData.items || [], this.layerName );

        this.callParent();

        // при загрузке вкладок вызвать перехватчик
        processManager.addEventListener( 'tabs_load', this.Builder.path, this.blockPostSending, this );

        // добавление полей фильтров
        this.resetFilters( this.ifaceData['barElements'] );

        //при выборе фильтра вызвать перехватчик
        processManager.addEventListener( 'form_filter_selected', this.Builder.path, this.blockPostSending, this );

        // при уничтожении снять прослушку события загрузки вкладок
        this.on('destroy',function(self){
            processManager.removeEventListener( 'tabs_load', self.Builder.path );
        });

    },

    /**
     * Отдает строку с типом компонента list / show / form
     * @returns {string}
     */
    getType: function(){
        return 'form';
    },

    /**
     * Переустановить набор фильтров
     * @param newItems
     */
    resetFilters: function( newItems ) {

        var form = this,
            itemId, item;

        if ( !newItems.length )
            return;

        // перебрать все пришедшие поля
        for (itemId in newItems) {

            item = newItems[itemId];

            // добавление метода выполнения поиска
            item.doSearch = function(){
                form.doSearch();
            };

            // если задан модуль - провести инициализацию сразу
            if ( item['libName'] )
                newItems[itemId] = Ext.create(item['libName'],item);

        }

        this.addDocked( {
            xtype: 'toolbar',
            dock: 'top',
            items: newItems
        } );

    },

    /**
     * Вызывает сохранение формы
     */
    callSaveState: function() {

        var saveStateName = this.ifaceData['saveStateName'] || null;
        if ( !saveStateName )
            return;

        // вылезти выше
        var toolbarList = this.Builder.cont.getDockedItems("toolbar");
        var toolbar = toolbarList[0];

        if ( !toolbar )
            return;

        var items = toolbar.items.items;
        var saveItem = null;

        // найти заданную кнопку
        for ( var i in items ) {
            if ( items[i].state == saveStateName )
                saveItem = items[i];
        }

        if ( !saveItem )
            return;

        // вызвать её обработчик
        saveItem.handler();

    },

    /**
     * Выполнение после инициализации
     */
    execute: function( data, cmd ){

        var itemList;
        var key, field;
        var fieldList;
        var name, item;

        switch ( cmd ) {

            case 'loadItem':

                fieldList = this.getForm().getFields().items;

                for (key in fieldList) {

                    if ( !fieldList.hasOwnProperty(key) )
                        continue;

                    field = fieldList[key];
                    name = field.getName();

                    item = data['items'][name];
                    if ( !item )
                        continue;

                    if ( (item['store'] != undefined) && (item['store']['data'] != undefined) )
                        field.getStore().loadData( item['store']['data'] );

                    if ( typeof item['value'] != 'undefined' )
                        field.setValue( item['value'] );

                    if ( typeof item['readOnly'] != 'undefined' )
                        field.setReadOnly(item['readOnly']);

                    if ( typeof item['disabled'] != 'undefined' )
                        field.setDisabled( item['disabled'] );

                    // Неактивное поле
                    if ( typeof item['hidden'] != 'undefined' )
                        field.setVisible( !item['hidden'] );

                    // Изменить заголовок поля
                    if ((item['title'] != 'undefined') && (item['title'] != '') && (item['title'] != name && field.labelEl))
                        field.labelEl.dom.innerHTML = item['title'] + ':';
                }

                break;

            default:
                // перебрать все поля
                itemList = this.items.items;
                for (key in itemList) {
                    field = itemList[key];
                    // выполнить спец обработку, если есть
                    if (typeof field.execute === 'function')
                        field.execute(arguments, key);
                }

                fieldList = this.getForm().getFields().items;

                for (key in fieldList) {

                    field = fieldList[key];

                    if ( field['onUpdateAction'] ) {
                        field.on('focus', this.onFieldFocus);
                        field.on('change', this.onFieldBlur);
                    }

                }

        }

    },

    /**
     * Метод, вызываемый для поля при выделении
     * сохраняет значение при выделении и при потере фокуса можно определить были
     * ли изменения после выделения и до потери фокуса
     */
    onFieldFocus: function( field ) {
        field.valOnFocus = field.getValue();
    },

    /**
     * Метод, вызываемый для поля при обновлении, если оно помечено как активное
     */
    onFieldBlur: function( field ) {

        // пропустить, если есть старое значение, равное текущему
        if ( field.valOnFocus === field.getValue() )
            return;

        field.valOnFocus = field.getValue();

        var action = field['onUpdateAction'] || '';
        var loadingAnimation = field['loadingAnimation'] == undefined ? true : field['loadingAnimation'];
        var form = field.up('form');
        var rootCont = processManager.getMainContainer(field);

        // данные к отправке
        var dataPack = {};
        Ext.merge(
            dataPack,
            rootCont.serviceData,
            {
                cmd: action,
                from: 'form',
                fieldName: field.getName(),
                fieldValue: field.getValue(),
                fieldOldValue: field.originalValue,
                formData: form.getValues()
            }
        );

        processManager.setData(rootCont.path,dataPack);

        if ( loadingAnimation )
            rootCont.setLoading(true);

        processManager.postData();

    },

    /**
     * Получение данных при действии
     */
    getData: function( button ){

        // выяснить не отключена ли блокировка
        if ( !button['unsetFormDirtyBlocker'] ) {

            this.blockPostSending();

        }

        return { data: this.getValues() };

    },

    blockPostSending: function() {

        if ( Ext.isIE7 )
            return;

        var form = this;

        //// ищет "загрязненные поля"
        //form.getForm().getFields().findBy(function(f) {
        //    if ( f.isDirty() )
        //        console.log( f );
        //    return false;
        //});

        // если были внесены изменения
        if ( form.getForm().isDirty() && form.trackChanges ) {

            // создать перехват
            var key = processManager.interceptPostData();

            // запросить действие

            sk.confirmWindow(sk.dict('editorCloseConfirmHeader'), sk.dict('editorCloseConfirm'), function(res){
                if ( res === 'yes' ) {
                    // отправить данные
                    processManager.postInterceptedData( key );
                } else {
                    // сбросить посылку
                    processManager.terminateInterceptedData( key );
                }
            }, this );

        }

    },

    doSearch: function() {

        var values = {},
            item,
            grid,
            rootCont,
            toolbar
            ;

        processManager.fireEvent( 'form_filter_selected', '', this.path );

        if ( this.is('panel') )
            grid = this;
        else
            grid = this.up('panel');

        rootCont = grid.up('panel').up('panel');
        toolbar  = grid.down('toolbar');

        // если есть панель фильтров
        if ( toolbar ) {

            // набор элементов фильтров
            var items = toolbar.items.items;

            // собрать все фильтры воедино
            for ( var itemKey in items ) {

                // ссылка на элемент
                item = items[itemKey];

                // если есть метод запроса одиночного значения
                if ( typeof(item.getFilterName)==='function' )
                    values[ item.getFilterName() ] = item.getFilterValue();

                // если есть метод группрвого запроса
                if ( typeof(item.getGroupFilter)==='function' ) {
                    item.getGroupFilter(function( name, val ){
                        values[name] = val;
                    });
                }

            }

        }

        // задать комманду обновления
        values.cmd = grid.ifaceData['actionNameLoad'];

        // установить индикатор загрузки
        rootCont.setLoading(true);

        // данные к отправке
        var dataPack = {
            from: 'form'
        };
        Ext.merge(dataPack, rootCont.serviceData, values);

        processManager.setData(rootCont.path,dataPack);
        processManager.postData();
        rootCont.setLoading(true);

    }

});

// Перекрываем метод из библиотеки ExtJs
Ext.isIterable = function(value) {
    // Добавляем условие, если объект содержит элемент с ключом length
    if (value && typeof value.length == 'string') return false;
    return (value && typeof value !== 'string') ? value.length !== undefined  : false;
};
