/**
 * Класс для отображения набора фотографий
 */
Ext.define('PhotoListModel', {
    extend: 'Ext.data.Model',
    fields: [
       {name: 'name'},
       {name: 'url'},
       {name: 'size', type: 'float'},
       {name:'lastmod', type:'date', dateFormat:'timestamp'}
    ]
});

Ext.define('Ext.Adm.PhotoListView', {

    extend: 'Ext.view.View',

    border: 0,
    cls: 'js_adm_gallery',
    overItemCls: 'x-item-over',
    itemSelector: 'div.thumb-wrap',
    emptyText: '<div class="empty-cont">empty</div>',
    clickAction: '',
    enableDragDrop   : true,
    ddGroup     : 'picDDGroup',
    multiSelect: true,
    plugins: [
        Ext.create('Ext.ux.DataView.DragSelector', {}),
        Ext.create('Ext.Component',{
            view: null,
            delSelector: 'thumb-del',
            init: function(view) {
                this.mon(view, 'render', this.bindEvents, this);
                this.view = view;
            },
            bindEvents: function() {
                this.mon(this.view.getEl(), {
                    dblclick: {
                        fn: this.onDblClick,
                        scope: this
                    },
                    click: {
                        fn: this.onClick,
                        scope: this
                    }
                });
            },
            onDblClick: function(e, target) {
                var me = this;

                // клавиши для выделения
                if ( e.ctrlKey || e.shiftKey )
                    return;

                var item = me.view.findItemByChild(target);
                if ( !item )
                    return;
                var record = me.view.store.getAt(me.view.indexOf(item));

                if (Ext.fly(target).hasCls(me.delSelector)) {
                    me.startEdit(target, record.data[me.dataIndex]);
                    me.activeRecord = record;
                } else {
                    me.view.openItem( record );
                }
            },
            onClick: function(e, target) {

                var item_id = 0;
                
                var classList = target.className ? target.className.split(' ') : [];

                var index = Ext.Array.indexOf(classList, 'js_img_check');

                if ( index != -1 )
                    item_id = parseInt(target.name.substr(8));

                if( item_id ){
                    var container = processManager.getMainContainer(this.view);
                    var data = {};
                    data.cmd = 'photoActiveChange';
                    data.data = item_id;
                    processManager.setData(container.path,data);
                    processManager.postData();

                }
            }
        })
    ],

    store: {
        model: 'PhotoListModel',
        data: []
    },

    initComponent: function(){
        var me = this;
        me.callParent();
        me.emptyText = '<div class="empty-cont">' + me.lang.galleryNoImages + '</div>';
    },

    /**
     * Отдает набор id выбранных элементов
     */
    getSelectedIdList: function() {

        var me = this;

        var selection = me.getSelectionModel().getSelection();
        var sectionList = [];
        for ( var row in selection )
            sectionList.push( parseInt( selection[row].get('id') ) );

        return sectionList;

    },

    /**
     * HTML шаблон для элемента
     */
    tpl: [
        '<tpl for=".">',
            '<div class="thumb-wrap x-thumb-wrap" id="horizontal_sort{id}">',
                '<div class="thumb"><img class="handle" id="handle_horizontal_sort{id}" src="{url}" title="{name}"></div>',
                '<span class="x-editable">{shortName}</span>',
                '<div class="x-img_check"><label for="hact_img{id}">{active_label}:</label>&nbsp;<input class="js_img_check" type="checkbox" name="hact_img{id}" id="hact_img{id}" value="1" {active}></div>',
            '</div>',
        '</tpl>',
        '<div class="x-clear"></div>'
    ],
    openItem: function(record){
        var container = this.up('panel').up('panel');
        var data = container.up('panel').serviceData || {};
        data.cmd = this.clickAction;
        data.data = record.data;
        processManager.setData(container.path,data);
        processManager.postData();
    },

    prepareData: function(data) {
        Ext.apply(data, {
            shortName: data.name ? Ext.util.Format.ellipsis(data.name, 220) : '&nbsp;',
            sizeString: Ext.util.Format.fileSize(data.size),
            dateString: Ext.util.Format.date(data['lastmod'], "m/d/Y g:i a"),
            active_label: this.lang.galleryActive
        });
        return data;
    }

});