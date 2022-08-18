/**
 * Перекрытие медода для корректной работы выделения строк
 */
Ext.override(Ext.selection.RowModel,{

    bindComponent: function(view) {
        var me = this;

        me.views = me.views || [];
        me.views.push(view);
        me.bind(view.getStore(), true);

        view.on({
            itemclick: me.onRowMouseDown,
            scope: me
        });


        if (me.enableKeyNav) {
            me.initKeyNav(view);
        }
    }
});

/** Перекрытие, убирающее букву s в конце текста при выборе нескольких строк */
Ext.override(Ext.view.DragZone, {
    getDragText: function() {
        var count = this.dragData.records.length;
        return Ext.String.format(this.dragText, count, '');
    }
});

/**
 * Перекрытие ф-ции, которая некорректо перетаскивает (drag & drop) элементы списка в разных группах.
 * Особенно если в группе один элемент, который нужно перетащить в другую
 */
var handleNodeDrop_original = Ext.grid.ViewDropZone.prototype.handleNodeDrop; // Сохранить оригинальный метод
Ext.override(Ext.grid.ViewDropZone, {
    handleNodeDrop : function(data, record, position) {

        if (!this.groupslist_allowSorting)
            handleNodeDrop_original.apply(this, arguments);
    }
});

/**
 * Автопостроитель интерфейсов
 * Класс для работы со списками
 */

Ext.define('Ext.Builder.List', {

    extend: 'Ext.grid.Panel',

    requires: [
        'Ext.ux.CheckColumn'
    ],

    cls: 'sk-tab-list',

    width: '100%',
    height: '100%',
    border: 0,
    flex: 1,

    multiSelect: false,

    plugins: [],
    ifaceData: {},
    pagerLoaded: false,
    listeners: {
        // для операций редактирования в списковом режиме
        edit: function( editor, e ) {
            if ( !e.record.dirty ) return;
            var self = e.column;
            processManager.getMainContainer(self).setLoading(true);
            processManager.sendDataFromMainContainer( self, {
                cmd: self['listSaveCmd'],
                from: 'field',
                data: e.record.data,
                field_name: self.dataIndex
            } );
        },

        // Событие, возникающее когда построился список записей таблицы
        viewready: function(){

            // НАЧАЛО: Разворачивание определённых групп в списке
            if (this.groupslist_openedGroups)
                if (this.features[0] != undefined && this.features[0].ftype == 'groupingsummary') {

                    var sGroupClass = this.features[0].eventSelector.replace(/^\.+/gm,'');
                    var aGroupsDom = document.getElementsByClassName(sGroupClass);

                    for (i in aGroupsDom) {

                        sGroupTitle = '';
                        if (aGroupsDom[i].childNodes != undefined)
                            if (aGroupsDom[i].childNodes.length)
                                if (aGroupsDom[i].childNodes[0].childNodes.length)
                                    sGroupTitle = aGroupsDom[i].childNodes[0].childNodes[0].innerText.trim();

                        if (this.groupslist_openedGroups.indexOf('|' + sGroupTitle + '|') > -1) {
                            // Развернуть группу
                            groupBd = Ext.fly(aGroupsDom[i].nextSibling, '_grouping');
                            this.features[0].expand(groupBd);
                        }
                    }
                }
            // КОНЕЦ: Разворачивание определённых групп в списке

            // НАЧАЛО: Выделение определённых записей в списке.
            /*  Принцип работы:
                В пришедших полях ищется параметр, удовлетворяющий условию в highlighting_list_item
                У этого поля находится dom-элемент строки(tr) и каждому потомку (td) этой строки устанавливается стиль выделения,
                а так же добавляется всплывающая подсказка через оборачивание потомка div в тэг span с параметром title */

            if (this.highlighting_list_item == undefined) return;

            /** Шаблон dom-элемена всплывающей подсказки */
            var oSpanHintTpl = document.createElement('span');
            oSpanHintTpl.title = 'Hello';

            // Перебор всех записей таблицы
            for (iRowId in this.ifaceData.items) {

                // Перебор всех условий выделения строк
                for (sField in this.highlighting_list_item) {

                    /** параметры строки таблицы */
                    var oRowItem = this.ifaceData.items[iRowId];
                    var oCond = this.highlighting_list_item[sField];

                    // Преобразовать условия к виду строки с разделителями | для поиска в нескольких значениях
                    var sCondition = '|' + oCond.condition.split(',').join('|') + '|';

                    // Проверить условие и пропустить, если не подходит
                    if (sCondition.indexOf('|' + String(oRowItem[sField]) + '|') < 0)
                        continue;

                    /** Dom-элемент строки таблицы */
                    var oRowItemDom = this.getView().all.item(iRowId).dom;

                    // Перебрать все колонки текущей строки
                    for (var iChildItem in oRowItemDom.childNodes) {

                        /** Колонка обрабатываемой строки */
                        var oColumn = oRowItemDom.childNodes[iChildItem];
                        if (oColumn.nodeType != 1) continue;

                        // Применить стили выделения
                        Ext.DomHelper.applyStyles(oColumn, oCond.style);

                        // Обернуть dom-элемент текущей колонки всплывающей подсказкой
                        if (oCond.hint) {
                            var oSpanHintNew = oSpanHintTpl.cloneNode(true);
                            oSpanHintNew.title = oCond.hint;
                            oSpanHintNew.appendChild(oColumn.childNodes[0]);
                            oColumn.appendChild(oSpanHintNew);
                        }
                    }
                }
            }
            // КОНЕЦ: Выделение определённых записей в списке.
        }
    },

    /**
     * Отдает строку с типом компонента list / show / form
     * @returns {string}
     */
    getType: function(){
        return 'list';
    },

    initComponent: function() {

        var data = this.ifaceData;

        // инициализация сортировки переносом (drag and drop)
        this.initDD( data );

        // группировка записей
        this.initGrouping(data);

        // хранилище
        this.initStore(data);

        // инициализация постраничного
        this.initPager(data);

        // набор колонок
        this.initColumns(data);

        // инициализация галочек для множественных операций
        this.initCheckboxSelection( data );

        // создать объект
        this.callParent();

        // добавление полей фильтров
        this.resetFilters( data['barElements'] );

        // добавить данные для отображения
        this.getStore().loadData( data['items'] || [] );

        // подкрасить строки спец классами
        this.highlightRows();

    },

    /**
     * инициализация сортировки переносом (drag and drop)
     * @param data
     */
    initDD: function ( data ) {

        if ( data['ddAction'] ) {
            this.ddAction = data['ddAction'];
            this.viewConfig = {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    ddGroup: 'firstGridDDGroup'
                },
                listeners: {
                    drop:  this.ddEvent,
                    beforedrop: function(node, data, overModel, dropPosition, dropFunction, eOpts) {
                        var parent = this.up();

                        // Если разрешено перетаскивание между группами
                        if (parent.groupslist_allowSorting)
                            return true;

                        // Перетаскивание только элементов одной группы
                        return (data.records[0].data[parent.groupslist_groupField] == overModel.data[parent.groupslist_groupField]);
                    }
                }
            }
        }

    },

    ddEvent: function(node, data, dropRec, dropPosition) {
        var self = this;

        var sendData = {};
        if (!self.up().multiSelect){
            sendData = data.records[0].data;
        }else{
            var items = [];
            Ext.each(self.up().getView().getSelectionModel().getSelection(), function(selectItem){
                items.push(selectItem.data)
            });
            if (items.length){
                sendData.items = items;
                sendData.multiple = true;
            }
        }

        processManager.sendDataFromMainContainer( self, {
            cmd: this.up()['ddAction'],
            data: sendData,
            dropData: dropRec.data,
            position: dropPosition
        } );
    },

    /**
     * Инициализация галочек для множественных операций
     * @param data
     */
    initCheckboxSelection: function ( data ) {

        if ( data['checkboxSelection'] ) {
            var me = this;

            me.multiSelect = true;
            me.selModel = Ext.create('Ext.selection.CheckboxModel', {
                loadMask: false,
                model:'MULTI',
                enableKeyNav: false,
                checkOnly: false,
                allowDeselect: true,
                ignoreRightMouseSelection: true,
                listeners: {
                    selectionchange: function(sm, selections) {
                        var items = me.up().down('toolbar').items;
                        Ext.each(items.items, function(item){
                            if (item.multiple){
                                item.setDisabled(selections.length === 0)
                            }
                        });
                    }
                },
                onRowMouseDown: function(view, record, item, index, e) {
                    view.el.focus();
                    var me = this,
                        checker = e.getTarget('.' + Ext.baseCSSPrefix + 'grid-cell-special'); // заменено для расширения области нажатия
                        // checker = e.getTarget('.' + Ext.baseCSSPrefix + 'grid-row-checker');

                    if (!me.allowRightMouseSelection(e)) {
                        return;
                    }


                    if (me.checkOnly && !checker) {
                        return;
                    }

                    if (checker && !e.shiftKey) {
                        var mode = me.getSelectionMode();


                        if (mode !== 'SINGLE') {
                            me.setSelectionMode('SIMPLE');
                        }
                        me.selectWithEvent(record, e);
                        me.setSelectionMode(mode);
                    } else {
                        me.selectWithEvent(record, e);
                    }
                }
            });

        }

    },

    /**
     * Инициализация группировки
     * @param data пришедшая посылка
     */
    initGrouping: function(data){

        if ( this.groupslist_groupField ) {
            // Внимание! Объект groupingsummary в массиве this.features всегда должен быть по индексом 0, т. к. используется выше
            this.features = [{
                id: this.groupslist_groupField,
                ftype: 'groupingsummary',
                groupHeaderTpl: '{name}',
                //hideGroupedHeader: true,
                remoteRoot: 'summaryData',
                startCollapsed: Boolean(this.groupslist_startCollapsed)
            }];
        }
    },

    /**
     * Инициализация хранилища
     * @param data пришедшая посылка
     */
    initStore: function(data){

        var onPage = data['itemsOnPage'] || 0,
            pageNum = (data['pageNum'] || 0)+1,
            totalCnt = data['itemsTotal'] || 0;

        this.store = Ext.create('Ext.data.Store',{
            pageSize: onPage,
            currentPage: pageNum,
            totalCnt: totalCnt,
            clearOnPageLoad: false,
            /** Убрали, т. к. мешает галочке в шапке */
            /*getCount: function(){ return onPage; },*/
            getTotalCount: function(){ return this.totalCnt; },
            fields: data['storeModel'] || [],
            sorters: data['sorters'] || [],
            groupField: this.groupslist_groupField || '',
            data: []
        });

    },

    /**
     * Инициализация постраничного
     * @param data пришедшая посылка
     */
    initPager: function( data ) {

        var onPage = data['itemsOnPage'] || 0;

        // постраничный
        if ( onPage ) {
            this.dockedItems= [{
                xtype: 'pagingtoolbar',
                store: this.store,
                dock: 'bottom',
                actionName: data['actionNameLoad'],
                displayInfo: true,
                firstChange: true,
                listeners: {
                    change: this.onPageChange
                }
            }];
        }

    },

    /**
     * Инициализация колонок
     * @param data пришедшая посылка
     */
    initColumns: function(data){
        var self = this;

        var colId,
            xTypes = Ext.create('Ext.sk.FieldTypes').getTypesAsObject(),
            xType, rName
        ;

        this.columns = data['columnsModel'] || [];

        // расширение описания колонок
        var bEditing = false;
        for ( colId in this.columns ) {

            // пришедшее на обработку описание поля
            var oColumn = this.columns[colId];

            // интерфейсное описание поля
            xType = (oColumn['jsView'] && xTypes[oColumn['jsView']]) ? xTypes[oColumn['jsView']] : null;

            if (oColumn['jsView'] == 'addImg')
                xType = 'addImg';

            if (xType) {

                // добавление колонки картинок
                if (xType == 'addImg') {
                    this.columns[colId] = this.initImageBlock(data);
                    continue;
                }

                // если поле редактируемое
                if (oColumn['listSaveCmd'] && xType['listEditableSettings']) {
                    bEditing = true;
                    Ext.merge(oColumn, xType['listEditableSettings']);
                    if (oColumn['beforeInit'])
                        oColumn['beforeInit']();
                }

                // проверка наличия расширяющего описания
                else if (xType['listSettings'])
                    oColumn = Ext.merge(oColumn, xTypes[oColumn['jsView']]['listSettings']);

            }

            // проверка наличия спец обработчиков
            rName = oColumn['specRenderer'];
            if (rName && xType['rendererList'] && xType['rendererList'][rName])
                oColumn['renderer'] = xType['rendererList'][rName];


            if (oColumn['sortBy']) {
                oColumn.getSortParam = function () {
                    return this['sortBy'];
                };
            }

            // раскрасить ячейки колонок классами
            oColumn.tdCls = 'sk-list-' + oColumn['dataIndex']

            // Перекрыть метод сортировки по колонке
            oColumn.setSortState = function(state, skipClear, initial) {

                if (!state || initial) // Инициализация стрелки направления сортировки
                    return Ext.grid.column.Column.prototype.setSortState.apply(this, [state, skipClear, true]);

                oData = self.ifaceData;

                // Если это первое вхождение сортировки и определён метод сортировки
                if (!skipClear && oData.sort_columns_method && oData.itemsOnPage < oData.itemsTotal) {

                    processManager.sendDataFromMainContainer(this.up(), {
                        cmd: oData.sort_columns_method,
                        data: {
                            sort_column: this.dataIndex,
                            sort_position: state
                        }
                    });

                    return;
                }

                // Вызвать базовый метод
                Ext.grid.column.Column.prototype.setSortState.apply(this, arguments);
            }

            this.columns[colId] = oColumn;
        }

        if ( bEditing ) {
            var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 1,

                /** нужно, иначе не работают галочки выбора записи */
                beforeEdit : function(event) {
                    return !(event.column && event.column.isCheckerHd);
                }
            });
            this.plugins.push( cellEditing );

        }

        // добавление кнопок в строках
        this.initRowButtons(data);

    },

    addCellImage: function(val, cell, record) {

        var data = record.data;

        return '<img height="100px" src="'+data.preview_img+'">';

    },

    initImageBlock: function(){

        return {
            xtype: 'actioncolumn',
            flex: true,
            sortable: false,
            menuDisabled: true,
            renderer: this.addCellImage,
            items: []
        };
    },

    initRowButtons: function(data){

        var me = this;

        if ( data['rowButtons'] && data['rowButtons'].length) {
            // созадать контейнер для кнопок
            var rowButtons = {
                xtype: 'actioncolumn',
                width: 20*data['rowButtons'].length+4,
                sortable: false,
                menuDisabled: true,
                tdCls: 'b-btn-rare',
                items: []
            };

            // добавить все кнопки
            for (var rowBtnDefKey in data['rowButtons']) {
                var rowBtn = data['rowButtons'][rowBtnDefKey];
                rowBtn.cls = 'sk-tab-list-row-btn-'+rowBtn.action;

                if ( rowBtn['customBtnName'] ) {

                    var customLayer = rowBtn['customLayer'] ? rowBtn['customLayer'] : this.layerName;

                    rowBtn.lang = me.lang;

                    rowBtn = Ext.create( 'Ext.'+customLayer+'.'+rowBtn['customBtnName'], rowBtn );

                } else {

                    // обработчик нажатия
                    rowBtn.handler = this.buttonHandler;

                }

                rowButtons.items.push( rowBtn );
            }

            // двойной клик
            this.listeners.itemdblclick = this.onDblClick;

            this.columns.push( rowButtons );
        }

    },

    execute: function( data, cmd ){

        var store;

        switch ( cmd ) {

            // загрузка данных на страницу
            case 'loadPage':

                store = this.getStore();

                // загрузка данных
                store.loadData( data['items'] || [] );

                // обновить общее число
                store.totalCnt = data['itemsTotal'] || 0;

                break;

            case 'loadItem':

                var item,
                    itemList,
                    keyField,
                    index,
                    record,
                    i
                ;

                store = this.getStore();

                itemList = data.items || [];
                keyField = data['keyField'] || '';

                for ( i in itemList ) {
                    item = itemList[i];

                    if (keyField instanceof Array ){

                        index = store.findBy( function(rec){

                            for (var key in keyField ) {
                                if (rec.data[keyField[key]] != item[keyField[key]])
                                    return false;
                            }

                            return true;

                        } );

                        record = store.getAt( index );

                    }else{
                        index = store.find( keyField, item[keyField] );

                        record = store.getAt( index );
                    }






                    if ( record ) {

                        // обновление значений
                        record.set(item);

                        // снятие пометки об изменении
                        record.commit();

                    }

                }

                break;

        }

        if ( data['pageNum'] ) {
            this.updatePager();
        }

        this.Builder.setLoading(false);

    },

    /**
     * Обновление панели постраничного просмотра
     */
    updatePager: function(){

        // есть панель постраничного
        var pager = this.down('pagingtoolbar');
        if ( pager ) {
            var pageData,
                currPage,
                pageCount,
                afterText;

            if (!pager.rendered) {
                return;
            }

            pageData = pager.getPageData();
            currPage = pageData.currentPage;
            pageCount = pageData.pageCount;
            afterText = Ext.String.format(pager.afterPageText, isNaN(pageCount) ? 1 : pageCount);

            pager.child('#afterTextItem').setText(afterText);
            pager.child('#inputItem').setValue(currPage);
            pager.child('#first').setDisabled(currPage === 1);
            pager.child('#prev').setDisabled(currPage === 1);
            pager.child('#next').setDisabled(currPage === pageCount);
            pager.child('#last').setDisabled(currPage === pageCount);
            pager.child('#refresh').enable();
            pager.updateInfo();
        }

    },

    /**
     * Обработчик кнопок
     */
    buttonHandler: function( grid, rowIndex, colIndex, item ){

        var state = item.state || '';
        var action = item.action || '';
        var container = grid.up('panel');
        var addParams = item.addParams || {};
        var rootCont = processManager.getMainContainer(grid);
        var rec = grid.getStore().getAt(rowIndex);

        if ( action ) {

            // данные к отправке
            var dataPack = {};
            Ext.merge(
                dataPack,
                rootCont.serviceData,
                addParams,
                {
                    from: 'list',
                    cmd: action,
                    data: rec.data
                }
            );

            // функция отправки данных
            function postData(){
                processManager.setData(rootCont.path,dataPack);
                rootCont.setLoading(true);
                if ( item.doNotUseTimeout )
                    processManager.doNotUseTimeout();
                processManager.postData();
            }

            switch (state) {

                // обработка операции удаления
                case 'delete':

                    // удалить
                    var row_text = rec.get('title');

                    if ( !row_text )
                        row_text = rec.get('name');

                    if ( !row_text ) {
                        var titleField = '';
                        for ( var colId in container.columns ) {
                            var column = container.columns[colId];
                            if ( column && !column.hidden && column.dataIndex ) {
                                titleField = column.dataIndex;
                                break;
                            }
                        }
                        if ( titleField )
                            row_text = rec.get(titleField);
                    }
                    // подтверждение удаления
                    var $oMsg = Ext.MessageBox;

                    var cfg = {
                        title: sk.dict('delRowHeader'),
                        icon: 'ext-mb-question',
                        msg: sk.dict('delRow').replace('{0}', row_text),
                        buttonText: {
                            ok: 'Ок', yes: 'Да', no: 'Нет', cancel: 'Отмена'
                        },
                        buttons: $oMsg.YESNO,
                        callback: function(res){

                            if ( res !== 'yes' ) return;

                            // отправить данные
                            postData();

                        },
                        scope: $oMsg

                    };

                    $oMsg.confirm(cfg);

                    break;

                /* Спрашиваем разрешение на выполнения действия (текст в self.actionText) */
                case 'allow_do':

                    Ext.MessageBox.confirm(sk.dict('allowDoHeader'),item.actionText, function(res){
                        if ( res !== 'yes' ) return false;
                        postData();
                        return true;
                    } );

                    break;

                default:
                    // отправить данные
                    postData();
                    break;

            }

        }

        return true;

    },

    /**
     * Обновить набр записей по фильтрам
     */
    commitFilterValues: function( self, pageData ){

        // задать значение по умолчанию
        if ( !pageData )
            pageData = {};

        var values = {},
            item,
            grid,
            rootCont,
            toolbar
        ;

        if ( self.is('panel') )
            grid = self;
        else
            grid = self.up('panel');

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

        // установить страницу
        if ( pageData )
            values.page = pageData['currentPage']-1 || 0;

        // установить индикатор загрузки
        rootCont.setLoading(true);

        // данные к отправке
        var dataPack = {
            from: 'list'
        };
        Ext.merge(dataPack, rootCont.serviceData, values);

        processManager.setData(rootCont.path,dataPack);
        processManager.postData();
        rootCont.setLoading(true);

    },

    /**
     * При изменении номера страницы просмотра
     */
    onPageChange: function( self, pageData ){

        var tab = self.up('panel');

        // контейнеры
        var grid = self.up('panel');

        if (this.store.currentPage > 1 && !this.store.data.length){
            pageData['currentPage'] = this.store.currentPage - 1;
            grid.commitFilterValues( self, pageData );
        }

        // pager hack - иначе срабатывет загрузка при инициализации
        if ( !tab.pagerLoaded ) {
            tab.pagerLoaded = true;
            return false;
        }

        // вызвать перегрузку элементов
        grid.commitFilterValues( self, pageData );

        return true;

    },

    /**
     * Событие по двойному клику на строке
     * @param self
     * @param rec
     * @param item
     * @param index
     */
    onDblClick: function( self, rec, item, index ) {

        // перебрать колонки
        var columns = self.up('panel').columns || [];
        for ( var columnId in columns ) {

            var column = columns[columnId];

            // найти колонку с кнопками
            if ( column.xtype == 'actioncolumn' ) {

                var buttons = column.items || [];

                // найти кнопку редактирования
                for ( var btnId in buttons ) {

                    var button = buttons[btnId];

                    // найти кнопку редактирования
                    if ( button.state == 'edit_form' ) {

                        button.handler( self, index, 0, button );
                        // и выходим после первого найденного срабатывания
                        return;

                    }

                }

            }

        }

    },

    /**
     * Получение данных при действии
     */
    getData: function(){

        var selection = this.getView().getSelectionModel().getSelection();

        var data = {};
        if ( selection.length ) {
            if (!this.multiSelect){
                var row = selection.shift();
                data = row.data;
            }
            else{
                var items = [];
                Ext.each(selection, function(selectItem){
                    items.push(selectItem.data)
                });
                if (items.length){
                    data.items = items;
                    data.multiple = true;
                }
            }

        }

        return { data: data };

    },

    /**
     * Переустановить набор фильтров
     * @param newItems
     */
    resetFilters: function( newItems ) {

        var grid = this,
            itemId, item;

        if ( !newItems.length )
            return;

        // перебрать все пришедшие поля
        for (itemId in newItems) {

            item = newItems[itemId];

            // добавление метода выполнения поиска
            item.doSearch = function(){
                grid.doSearch();
            };
            item.cls = "sk-" + item.fieldName;

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
     * Раскрашивает строки с данными спец классами для удобства автоматической выборки
     */
    highlightRows: function () {

        this.view.doStripeRows = function(startRow, endRow) {

            if (this.stripeRows) {
                var rows   = this.getNodes(startRow, endRow),
                    rowsLn = rows.length,
                    i      = 0,
                    row;

                var store = this.up('panel').getStore();
                var data_row;

                for (; i < rowsLn; i++) {
                    row = rows[i];

                    row.className = row.className.replace(this.rowClsRe, ' ');
                    startRow++;

                    if (startRow % 2 === 0) {
                        row.className += (' ' + this.altRowCls);
                    }

                    data_row = store.getAt( row.rowIndex );

                    if ( data_row && data_row.data.id )
                        row.className += ' sk-list-id-' + data_row.data.id;

                }

            }
        };

    },


    doSearch: function() {

        // постраничный
        var grid = this,
            pager = grid.down('pagingtoolbar');

        // есть панель постраничного
        if ( pager ) {
            // перейти к первой странице с новыми фильтрами
            pager.moveFirst();
        } else {
            // нет - просто отослать запрос на обновление
            grid.commitFilterValues(this);
        }

    }

});
