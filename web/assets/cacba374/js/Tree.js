/**
 *  Библиотека для работы с деревом разделов
 *
 */  
Ext.define('Ext.Adm.Tree', {
    extend: 'Ext.tree.Panel',
    region: 'west',
    split: true,
    minWidth: 275,
    maxWidth: 400,
    collapsible: true,
    animCollapse: false,
    margin: '0 0 0 0',
    sectionId: 0,
    nowSectionId: 0,

    useHistory: true,
    showButtons: true,
    eventPrefix: 'section',

    rootVisible: false,
    rootSection: 0,
    multiTree: false,
    nodeToSelect: 0,
    addToRootNode: false,
    form: null,

    displayField: 'title',
    hideHeaders: true,
    columns:[],
    viewConfig: {
        plugins: [{
            ddGroup: 'organizerDD',
            ptype  : 'treeviewdragdrop'
        }],
        listeners: {
            beforedrop: function(node, data, overModel, dropPosition){
                return this.up('panel').onBeforeDrop( node, data, overModel, dropPosition );
            }
        }
    },

    /**
     * Обработчик переноса разделов в ветке
     * @param node
     * @param data
     * @param overModel
     * @param dropPosition
     * @return {Boolean}
     */
    onBeforeDrop: function( node, data, overModel, dropPosition ) {

        // основные данные операции
        var tree = this;
        var itemId = parseInt(data['records'][0]['data']['id']);
        var overId = parseInt(overModel['data']['id']);
        var itemParentId = parseInt(data['records'][0]['data']['parent']);
        var overParentId = parseInt(overModel['data']['parent']);

        // в корень копировать нельзя и переносить оттуда
        // xor ( a ? !b : b )
        if ( (overParentId ? !itemParentId : itemParentId) && dropPosition!='append' )
            return false;

        // собрать посылку
        processManager.setData( tree.path, {
            cmd: 'changePosition',
            direction: dropPosition,
            itemId: itemId,
            overId: overId
        });

        // выбрать элемент
        this.nowSectionId = 0;
        tree.findSection( itemId );

        processManager.postData();

        // установить индикатор загрузки
        tree.setLoading(true);

        return true;
    },

    // инициализация
    initComponent: function() {

        var self = this;
        var me = this;


        if ( !self.title ) {
            self.title = me.lang.treePanelHeader;
        }

        this.columns = [{
            xtype: 'treecolumn',
            text: 'Section',
            flex: 5,
            dataIndex: 'title',
            renderer: function( value, meta, rec ) {

                // для разделов типа "директория"
                if ( parseInt( rec.get('type') ) == 0 ) {

                    // видимость
                    var visible = parseInt( rec.get('visible'));
                    var section = parseInt( rec.get('id'));
                    switch ( visible ) {
                        case 1:
                        case 2:
                            meta.tdCls = 'tree-row-section sk-tree-'+section;
                            break;
                        case -1:
                            meta.tdCls = 'tree-row-container sk-tree-'+section;
                            break;
                        default:
                            meta.tdCls = 'tree-row-hidden';
                            break;
                    }

                    // ссылка / обычный раздел
                    var link = rec.get('link');
                    if ( link )
                        meta.tdCls += ' tree-row-link';

                }

                return value;
            }
        },{
            dataIndex: 'id',
            tdCls: 'tree-row-hidden',
            width: 25
        },{
            xtype: 'actioncolumn',
            width: 36,
            tdCls: 'tree-row',
//        listeners: {
//            beforerender: function(){
//
//                return false;
//            }
//        },
            items: [{
                getClass: function(icon,rowIndex,rec) {
                    var visible = parseInt( rec.get('visible'));
                    if ( visible<0 ) return '';
                    else return 'icon-edit';
                },
                handler: function(grid, rowIndex) {
                    var rec = grid.getStore().getAt(rowIndex);
                    var visible = parseInt( rec.get('visible'));
                    if ( visible<0 ) return true;
                    this.up('treepanel').showSectionForm( rec.data );
                    return false;
                }
            },{
//            getClass: function(value, meta, rec) {
//                var visible = parseInt(rec.data.visible);
//                return visible ? 'icon-visible' : 'icon-hidden';
//            },
//            handler: function(grid, rowIndex) {
//                var rec = grid.getStore().getAt(rowIndex);
//                var visible = parseInt(rec.data.visible);
//                this.up('panel').setLoading( true );
//                if ( visible ) {
//                    this.up('treepanel').changeVisibility( rec, 0 );
//                } else {
//                    this.up('treepanel').changeVisibility( rec, 1 );
//                }
//                return false;
//            }
//        },{
                getClass: function(icon,rowIndex,rec) {
                    var visible = parseInt( rec.get('visible'));
                    if ( visible<0 ) return '';
                    return 'icon-delete';
                },
                handler: function(grid, rowIndex) {

                    var rec = grid.getStore().getAt(rowIndex);
                    var visible = parseInt( rec.get('visible'));
                    if ( visible<0 ) return;
                    var self = this;
                    var tree = self.up('panel');
                    Ext.MessageBox.confirm(me.lang.treeDelRowHeader, sk.dict('delRow').replace('{0}', rec.get('title')) +'<br>'+me.lang.treeDelMsg,function(res){
                        if ( res !== 'yes' ) return;

                        // установить индикатор работы
                        tree.setLoadingIndicator();

                        // собрать посылку
                        processManager.setData(self.up('treepanel').path,{
                            cmd: 'deleteSection',
                            sectionId: rec.get('id')
                        });

                        tree.setLoadingIndicator();

                        // выполнить обработчик
                        processManager.fireEvent(tree.eventPrefix+'_delete', rec.get('id'));

                    });
                }
            }],
            sortable: false
        }];

        // набор кнопок
        this.addDockedButtons();

        // хранилище
        this.store = Ext.create('Ext.data.TreeStore', {
            id: processManager.getUniqueId(),
            fields: ['id','title','visible'],
            root: {
                id: self.rootSection,
                name: 'Sections',
                expanded: true,
                lang: this.lang
            },
            proxy: 'memory'
        });

        // ext события
        self.on({
            scope: self,
            beforeload: self.onLoad
        });
        // клик по элементу
        this.on('itemclick', this.onItemClick, this);
        this.on('collapse', this.onDeactivate, this);

        this.callParent();

        //processManager.addEventListener( 'dev_site', this.path, 'selectItem' );
        processManager.addEventListener( 'location_render', this.path, 'processToken' );
        processManager.addEventListener( 'location_set_value',this.path,'setToken');
        processManager.addEventListener( 'get_section_id',this.path,'getSectionId');
        processManager.addEventListener( 'reload_section',this.path,'reloadCurrentSection');
        processManager.addEventListener( 'reload_tree',this.path,'reloadTree');

    },

    // выполение обработки данных
    execute: function( data, cmd ){

        var me = this;
        var store, record, parent_node, key, parent,
            row, parentId, node;

        if ( data.error )
            sk.error( data.error );

        switch ( cmd ) {

            // загрузка подчиненных элементов
            case 'loadItems':

                var error = false;

                // перебрать все пришедшие строки

                if ( data.items ) {
                    var items = data.items;
                    for ( key in items ) {

                        // значения строки
                        row = items[key];

                        // запись с таким id
                        var rowWithId = this.store.getNodeById( row.id );
                        if ( rowWithId )
                            rowWithId.destroy();

                        // id родителя
                        parentId = parseInt(row.parent);

                        // родительская запись
                        parent = parentId ? this.store.getNodeById( parentId ) : this.store.getRootNode();

                        // есть - добавить к ней запись
                        if ( parent )
                            parent.appendChild(row);
                        else
                            error = true;

                        if ( parseInt(row.id) === parseInt(this.nowSectionId) ) {
                            this.nowSectionId = 0;
                            this.findSection( row.id );
                        }

                    }

                }

                // выдать ошибку, если не все строки были добавлены
                if ( error )
                    sk.error(me.lang.treeErrorNoParent);

                break;

            // создание формы
            case 'createForm':

                var form = data.form;

                // сборка перекрывающего массива
                var cover_params = {
                    form: form,
                    isNew: form.id,
                    tree: this,
                    treeStore: this.store,
                    lang: me.lang,
                    cls:'sk-section-add-form'
                };

                this.form = Ext.create('Ext.Adm.TreeForm', cover_params);

                break;

            // после сохранения данных
            case 'saveItem':

                var saveResult = data.saveResult || 0;
                var item = data.item || false;

                this.form.close();

                if ( saveResult && item ) {

                    // наследников нет
                    item.children = [];

                    store = this.down('treeview').getStore();
                    record = store.getById(item.id);
                    if ( record ) {

                        // обновление значений
                        record.set( item );

                        // снятие пометки об изменении
                        record.commit();

                        if ( item.id == this.nowSectionId )
                            this.reopenItem( item.id );

                    } else {

                        // найти родителя
                        parentId = parseInt(item.parent);
                        parent_node = parentId ?
                            this.store.getNodeById( parentId ) :
                            this.store.getRootNode()
                        ;

                        // если родительский раздел найден и развернут
                        if ( parent_node && parent_node.isExpanded() ) {

                            parent_node.appendChild( item );

                            this.selectItem( item.id );

                        } else {

                            this.findSection( item.id );

                        }

                    }

                }

                break;

            // удалние раздела
            case 'deleteSection':

                /** @namespace data.deletedId */
                var deletedId = data.deletedId || 0;

                if ( deletedId ) {

                    node = this.store.getNodeById( deletedId );

                    if ( node ) {

                        // родительский раздел
                        var parentNodeId = node.data['parentId'] || 0;

                        // удалить заданную вершину
                        node.destroy();

                        // подсветить родительскую
                        this.findSection( parentNodeId );

                        // если используется история
                        if ( this.useHistory ) {

                            // изменить контрольную точку страницы
                            this.sectionId = parentNodeId;
                            processManager.fireEvent('location_change');

                        }

                    } else {
                        sk.error( me.lang.treeErrorOnDelete );
                    }


                } else {

                    sk.error( me.lang.treeErrorOnDelete );

                }

                break;

            case 'loadTree':

                if (data['dropAll']) {
                    this.store.getRootNode().removeAll();
                }

                // обойти все элементы
                if ( data.items ) for ( key in data.items ) {

                    // значения строки
                    row = data.items[key];
                    parentId = parseInt(row.parent);

                    // текущая запись
                    node = this.store.getNodeById( row.id );

                    // нет - добавить
                    if ( !node ) {

                        // родительская запись
                        parent = parentId ? this.store.getNodeById( parentId ) : this.store.getRootNode();

                        // есть - добавить к ней запись
                        if ( parent )
                            parent.appendChild(row);

                    }

                }

                if (data['dropAll']) {
                    this.findSection(data.sectionId);
                } else {
                    this.selectNode(data.sectionId);
                }

                break;

            // выбор раздела
            case 'selectNode':

                this.selectNode( data.sectionId );

                break;

        }

        this.unsetLoadingIndicator();

    },

    /**
     * При сворачивании в интерфейсе
     */
    onDeactivate: function() {
        // чтобы при разворачивании сам загрузился
        this.itemId = 0;
        this.nowSectionId = 0;
        return true;
    },

    setLoadingIndicator: function(){
        this.setLoading( true );
    },

    unsetLoadingIndicator: function(){
        if ( this.form )
            this.form.setLoading( false );
        this.setLoading( false );
    },

    addDockedButtons: function(){
        var me = this;
        if ( this.showAdd ) {
            this.tbar = [{
                text: me.lang.add,
                iconCls: 'icon-add',
                scope: this,
                handler: this.addSection,
                cls:'sk-section-add'
            }];
        } else {
            this.tbar = [];
        }
    },

    showSectionForm: function( parameters ){

        this.setLoadingIndicator();

        // создать посылку
        processManager.setData( this.path, {
            cmd: 'getForm',
            selectedId: parameters.id || 0,
            itemId: parameters.id || 0,
            item: parameters
        });

        this.setLoadingIndicator();

        // отправить посылку
        processManager.postData();

    },

    // добавление нового раздела
    addSection: function() {

        var me = this;
        // родитель
        var parentNodeId;
        var selection = this.getSelectionModel();
        // если есть выбранный элемент
        if ( !this.addToRootNode && selection.getCount() ) {
            var sel = selection.getSelection()[0];
            parentNodeId = sel.getId();
        } else {
            // если корневой элемент не 0
            parentNodeId = this.getRootNode().getId();
            if ( !parseInt(parentNodeId) || !parentNodeId ) {
                // если 0 - выдать ошибку
                sk.error( me.lang.treeErrorParentNotSelected );
                return false;
            }
        }

        this.showSectionForm( {
            id: 0,
            title: me.lang.treeNewSection,
            parent: parentNodeId,
            type: this.defaultSectionType,
            visible: 1
        } );

        return true;

    },

    // событие при клике по элементу
    onItemClick: function(view, node, item, num, event ) {

        var newId,oldId;

        // только по клику на самом элементе
        if ( event.target.nodeName == 'IMG' )
            return false;

        // поставить блокировку отправки
        processManager.setBlocker();

        // развернуть
        if ( !node.isLoaded() &&  !node.isLoading() ) {
            // развернуть, если не раскрыто
            if (!node.isExpanded()) {
                node.expand(); // вызовет событие раскрытия и расставит ext параметры для ветки
            }
        }

        newId = parseInt(node.getId());
        oldId = this.sectionId;

        // выбранный раздел
        this.sectionId = newId;
        this.nowSectionId = oldId;

        if ( newId !== oldId ) {
            // если используется история
            if ( this.useHistory ) {

                // изменить контрольную точку страницы
                processManager.fireEvent('location_change');

            } else {

                // иначе просто перейти к разделу
                this.findSection( newId );

            }
        } else {

            processManager.fireEvent( 'tabs_load', newId, this.path );

        }

        // снять блокировку отправки
        processManager.unsetBlocker();

        // отправить данные
        processManager.postDataIfExists();

        return true;

    },

    /**
     * Выбор раздела в дереве
     * @param iSectionId
     */
    selectNode: function( iSectionId ) {
        var node = this.store.getNodeById( iSectionId );
        if ( node ) {
            if ( this.multiTree && this.collapsed) {
                this.nodeToSelect = iSectionId;
                return false;
            }
            if ( !this.rendered ) {
                this.nodeToSelect = iSectionId;
                return false;
            }
            this.selectPath( node.getPath() );
            this.sectionId = iSectionId;
            this.nowSectionId = iSectionId;
            return true;
        }
        return false;
    },

    /**
     * Находит в дереве раздел и автоматически его открывает
     * @param iSectionId
     */
    findSection: function( iSectionId ) {

        var me = this,
            reSelect = false
        ;

        if ( me.nowSectionId === iSectionId )
            return false;

        if ( !me.selectNode( iSectionId ) ) {
            // собрать посылку на загрузку ветки
            if ( me.nodeToSelect == iSectionId ) {
                reSelect = true;
                me.nodeToSelect = 0;
            }
            processManager.setData(me.path,{
                cmd: reSelect ? 'selectNode' : 'getTree',
                sectionId: iSectionId
            });
            me.setLoadingIndicator();
        }

        // вызвать событие выбора раздела
        processManager.fireEvent( 'tabs_load', iSectionId, me.path );

        return true;

    },

    /**
     * Выбирает заданный раздел для дерева
     * @param id
     */
    selectItem: function( id ) {

        var me = this;

        me.nodeToSelect = id;
        pageHistory.locationChange();

    },

    reopenItem: function(id) {
        var me= this;
        me.sectionId = 0;
        me.nowSectionId = 0;
        me.findSection(id);
    },

    /**
     * Рекурсивно открывает набор разделов
     * @param aSectionIds
     */
    recOpenSection: function( aSectionIds ){

        if ( !aSectionIds.length )
            return;

        // взять первый раздел
        var iSectionId = aSectionIds.pop();

        var tree = this;

        var parentNode = tree.store.getNodeById( iSectionId );
        parentNode.expand( false, function(){
            tree.recOpenSection( aSectionIds );
        });

    },

    /**
     * Метод, вызываемый при событии "загрузка ветки"
     *
     * @param store - хранилище
     * @param options - параметры
     */
    onLoad: function( store, options ) {

        options = options || {};
        options.params = options.params || {};

        var nodeId = options.params.node;

        // создать посылку
        processManager.setData( this.path, {
            cmd: 'getSubItems',
            node: nodeId
        });

        this.setLoadingIndicator();

        // выполнить подписанные события
        processManager.fireEvent( this.eventPrefix+'_list_load', nodeId );

    },

    /**
     * Сохранение записи
     * @param row - объект с данными для сохранения
     */
    saveRow: function( row ) {

        var tree = this;

        // дополнение сохраняемой строки базовыми значениями
        var base_params = self.parameters;
        for (var name in base_params) {
            if ( typeof row[name] === 'undefined' ) {
                row[name] = base_params[name];
            }
        }

        // собрать посылку
        processManager.setData(tree.path,{
            cmd: 'saveSection',
            sectionId: parseInt(row.id),
            item: row
        });

        tree.setLoadingIndicator();

        // выполнить обработчик
        processManager.fireEvent(this.eventPrefix+'_save', row);

    },

    /**
     * обработка токена истории
     */
    processToken: function( data ){

        var newId,oldId,
            me = this
        ;

        // идентификатор раздела
        newId = parseInt(data[me.path]);
        oldId = me.itemId;

        // проверки
        if ( !newId ) return false;
        if ( newId === oldId )
            return false;

        // выбранный раздел
        me.itemId = newId;

        // открыть раздел
        this.findSection( newId );

        return true;

    },

    /**
     * обработка добавления данных в токен истории страниц
     */
    setToken: function(){

        var me = this;

        if ( !this.useHistory || (this.multiTree && this.collapsed) ) return false;

        // если установлен "следующий" - взять его
        var id = me.nodeToSelect ? me.nodeToSelect : me.sectionId;
        me.nodeToSelect = 0;

        // задать данные
        processManager.setData(this.path,id,'locPack');

        return true;

    },

    /**
     * для события запроса id раздела
     * @return {Number|undefined}
     */
    getSectionId: function() {
        if ( !this.multiTree || !this.collapsed )
            return this.sectionId;
        else
            return undefined;
    },

    /**
     * Полностью перезагружает дерево разделов
     */
    reloadTree: function() {

        // основные данные операции
        var tree = this;

        // собрать посылку
        processManager.setData( tree.path, {
            cmd: 'reloadTree',
            sectionId: this.sectionId
        });

        this.sectionId = 0;
        this.nowSectionId = 0;

        processManager.postData();

    },

    /**
     * Перезагружает выбранный раздел
     */
    reloadCurrentSection: function() {
        if ( !this.multiTree || !this.collapsed ) {
            var section = this.getSectionId();
            if ( section ) {
                processManager.fireEvent( 'tabs_load', section, this.path );
            }
        }
    }

});
