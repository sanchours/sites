/**
 *  Библиотека для работы с деревом разделов
 *
 */  
Ext.define('Ext.Design.ParamTree', {
    extend: 'Ext.tree.Panel',
    region: 'west',
    split: true,
    width: 275,
    minWidth: 275,
    maxWidth: 400,
    margins: '5 0 5 5',
    useArrows: true,
    itemId: 0,

    hideHeaders: true,
    columns: [{
        xtype: 'treecolumn',
        text: 'Group',
        flex: 5,
        sortable: false,
        dataIndex: 'title',
        renderer: function( value, meta, rec ) {
            // для скрытых разделов
            if ( !parseInt( rec.get('visible') ) )
                meta.tdCls = 'tree-row-hidden';
            return value;
        }
    }],

    title: '',
    rootVisible: false,

    displayField: 'title',
    
    /**
     * Текущая версия контента
     */
    currentVersion: 'full',

    /**
     * Текущий обработанный url страницы отображения
     */
    currentDiplayUrl: '',

    // инициализация
    initComponent: function() {

        var me = this;

        me.title = me.lang.treePanelHeader;

        me.columns = [{
            xtype: 'treecolumn',
            text: 'Section',
            flex: 5,
            dataIndex: 'title',
            renderer: function( value, meta, rec ) {
                return value;
            }
        },{
            xtype: 'actioncolumn',
            width: 18,
            tdCls: 'tree-row',
            items: [{
                getClass: function(icon,rowIndex,rec) {

                    var canDelete = rec.get('canDelete');
                    if (canDelete)
                        return 'icon-delete';
                    else
                        return '';
                },
                handler: function(grid, rowIndex) {
                    var rec = grid.getStore().getAt(rowIndex);
                    var panel = this.up('panel');
                    if ( panel.onRemove )
                        panel.onRemove( rec.get('id') );
                    return false;
                }
            }],
            sortable: false
        }];

        // хранилище
        me.store = Ext.create('Ext.data.TreeStore', {
            fields: ['id','name','canDelete','title','visible','cnt'],
            root: {
                name: 'root',
                expanded: true
            },
            proxy: 'memory'
        });

        // клик по элементу
        me.on('itemclick', me.onItemClick, me);

        processManager.addEventListener('urlChange',me.path,me.onUrlChange);
        processManager.addEventListener('select_group',me.path,me.selectGroup);

        me.callParent();

    },

    // выполение обработки данных
    execute: function( data, cmd ){

        // проверить пришедший статус
        if ( typeof data['newVersion'] !== 'undefinde' ) {
            this.currentVersion = data['newVersion'];
        }

        switch ( cmd ) {

            // загрузка подчиненных элементов
            case 'init':

                // если есть строки
                if ( data.items ) {

                    // добавить
                    this.getStore().getRootNode().appendChild( data.items );

                    // сообщить о загрузке дерева
                    processManager.fireEvent('tree_loaded');

                }

                break;

            case 'loadItems':

                // если есть строки
                if ( data.items ) {

                    var rootNode = this.getStore().getRootNode();

                    // очистить дерево
                    rootNode.removeAll();

                    // добавить
                    rootNode.appendChild( data.items );

                    // сообщить о загрузке дерева
                    processManager.fireEvent('tree_loaded');

                }

                break;

        }

        this.setLoading( false );

    },
    onRemove: function( id ) {
        var me = this;
        Ext.MessageBox.confirm(designLang.paramsRemoveParamTitle, designLang.paramsRemoveParam,function(res){
            if ( res !== 'yes' ) return false;

            processManager.setData(me.path,{
                cmd: 'removeGroup',
                id: id
            });
            processManager.postData();

            return true;
        });

        },

    /**
     * Выбирает группу
     * @param groupId
     */
    selectGroup: function( groupId ) {

        var me = this;

        // выбрать текущую вкладку
        var tab = me.up('panel');
        var tabs = tab.up('panel');
        tabs.setActiveTab(tab);

        // закрыть все
        me.collapseAll();

        // найти целевой раздел
        var node = this.store.getNodeById( groupId );

        if ( node ) {

            // раскрыть дерево до заданной вершины
            me.recOpenSection( node.getPath().split('/') );

            // выбрать элемент
            me.findNotEmptySection( groupId );


        } else {
            sk.error( designLang.paramsGroupNotFound );
        }

    },

    /**
     * Рекурсивно открывает набор элементов
     * @param aSectionIds
     */
    recOpenSection: function( aSectionIds ){

        if ( !aSectionIds.length )
            return;

        // взять первый раздел
        var iSectionId = parseInt( aSectionIds.pop() );

        var tree = this;

        if ( iSectionId ) {
            var parentNode = tree.store.getNodeById( iSectionId );
            parentNode.expand( false, function(){
                tree.recOpenSection( aSectionIds );
            });
        } else {
            tree.recOpenSection( aSectionIds );
        }

    },

    /**
     * Вызывается при обновлении страницы просмотра
     */
    onUrlChange: function( nowUrl ){

        var me = this;
        processManager.setData( me.path, {
            cmd: 'checkVersion',
            ver: me.currentVersion,
            url: nowUrl
        });

    },
    
    // событие при клике по элементу
    onItemClick: function(view, node, item, num, event ) {

        // не активировать при нажатии на спец обработчик строки
        if ( event.target.nodeName == 'IMG' )
            return false;

        // развернуть, если не раскрыто
        if (!node.isExpanded()) {
            node.expand();
        }

        // выбранный раздел
        this.itemId = node.getId();

//      Ext.History
//        // изменить контрольную точку страницы
//        processManager.fireEvent('location_change');

        this.findSection( node.getId() );

        return false;

    },

    /**
     * Выбор раздела в дереве
     * @param iId
     */
    selectNode: function( iId ) {
        var node = this.store.getNodeById( iId );
        if ( node ) {
            this.selectPath( node.getPath() );
            return true;
        }
        return false;
    },

    /**
     * Находит в дереве раздел и автоматически его открывает
     * @param iId
     */
    findSection: function( iId ) {

        // выбрать группу
        this.selectNode( iId );

        // вызвать событие выбора раздела
        processManager.fireEvent( 'group_select', iId );

    },

    /**
     * Находит первый не пусто раздел из заданной ветки
     * @param id
     */
    findNotEmptySection: function( id ) {

        var me = this;

        // попробоать найти непустой раздел
        var current = me.store.getNodeById(id);
        var limit = 10;
        while ( current && !parseInt(current.get('cnt')) && --limit ) {
            if ( current.childNodes && current.childNodes[0] )
                current = current.childNodes[0];
        }

        me.findSection( current ? current.get('id') : id );

    }

});
