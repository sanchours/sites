/**
 * Форма для создания и редактирования разделов
 */
Ext.define('Ext.Adm.TreeForm', {
    extend: 'Ext.window.Window',
    title: '',
    isNew: true,
    tree: null,
    treeStore: null,
    form: {},
    template_list: [],
    frame: false,
    closable: true,
    width: 450,
    closeAction: 'destroy',
    modal: true,
    parameters: {
        id: 0,
        alias: '',
        title: '',
        parent: 0,
        template: 0,
        link: '',
        visible: 0
    },
    bodyStyle: 'padding: 5px;',
    //items: {},

    items: {
        id: 'form',
        region: 'center',
        xtype: 'form',
        fieldDefaults: {
            labelAlign: 'left',
            labelWidth: 140,
            anchor: '100%',
            margin: '5px'
        }
    },

    sendData: function(me){

        // выйти, если данные не проходят валидацию
        if (!me.up('form').getForm().isValid())
            return;

        // сохраняемый набор данных
        var row = me.up('form').getForm().getValues();

        var window = me.up('window');
        me.up('window').tree.saveRow( row, function(){

            // закрыть окно с формой
            //noinspection JSUnresolvedFunction
            window.close();

        } );
    },

    generateItems: function(){

        var me = this;

        me.items = {
            id: 'form',
            region: 'center',
            xtype: 'form',
            fieldDefaults: {
                labelAlign: 'left',
                labelWidth: 140,
                anchor: '100%',
                margin: '5px'
            },
            items: [{
                name: 'id',
                xtype: 'hiddenfield'
            }, {
                name: 'title',
                xtype: 'textfield',
                fieldLabel: me.lang.treeFormTitleTitle,
                allowBlank: false,
                value: '',
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            return me.sendData(this);
                        }
                    }
                }
            }, {
                name: 'alias',
                xtype: 'textfield',
                fieldLabel: me.lang.treeFormTitleAlias,
                allowBlank: true,
                value: '',
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            return me.sendData(this);
                        }
                    }
                }
            }, {
                name: 'parent',
                xtype: 'combo',
                fieldLabel: me.lang.treeFormTitleParent,
                mode: 'local',
                value: '',
                triggerAction: 'all',
                forceSelection: true,
                allowBlank: false,
                editable: false,
                displayField: 'title',
                valueField:'id',
                queryMode: 'local',
                cls:'sk-parent-section',
                store: Ext.create('Ext.data.Store', {
                    fields: ['id','title'],
                    data: []
                })
            }, {
                name: 'template',
                xtype: 'combo',
                fieldLabel: me.lang.treeFormTitleTemplate,
                mode: 'local',
                value: '',
                triggerAction: 'all',
                forceSelection: true,
                //allowBlank: false,
                editable: false,
                displayField: 'title',
                valueField:'id',
                queryMode: 'local',
                store: Ext.create('Ext.data.Store', {
                    fields: ['id','title'],
                    data: []
                })
            }, {
                name: 'link',
                xtype: 'textfield',
                fieldLabel: me.lang.treeFormTitleLink,
                value: '',
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            return me.sendData(this);
                        }
                    }
                }
            }, {
                name: 'visible',
                xtype: 'combo',
                fieldLabel: me.lang.treeTitleVisible,
                mode: 'local',
                value: '',
                triggerAction: 'all',
                forceSelection: true,
                allowBlank: false,
                editable: true,
                displayField: 'title',
                valueField:'id',
                queryMode: 'local',
                store: Ext.create('Ext.data.Store', {
                    fields: ['id','title'],
                    data: [{
                        id: 1,
                        title: me.lang.visibleVisible
                    },{
                        id: 0,
                        title: me.lang.visibleHiddenFromMenu
                    },{
                        id: 2,
                        title: me.lang.visibleHiddenFromPath
                    },{
                        id: 3,
                        title: me.lang.visibleHiddenFromIndex
                    }]
                })
            }],
            buttons: [{
                text: me.lang.paramFormSaveUpd,
                id:'send_button',
                cls:'sk-save-new-section',
                handler: function() {
                    return me.sendData(this);
                }
            },
                {
                    text: me.lang.paramFormClose,
                    handler: function() {
                        this.up('window').close();
                    }
                }
            ]
        }
    },

    initComponent: function(){

        var me = this;

        me.generateItems();

        // генерация объекта
        this.callParent();
        me.title = me.lang.treeFormHeaderAdd;

        // изменение заголовка при редктировании
        if( this.isNew ) {
            this.title = me.lang.treeFormHeaderUpd;
        }

        // формирование выпадающего списка групп
        var form = this.down('form').getForm();

        // набор шаблонов
        var templateField = form.findField('template');
        var templateList = this.form.template_list || [];
        templateField.store.loadData( templateList );
        if( this.isNew ) {
            templateField.disable();
        }

        // набор родительских разделов
        var parentField = form.findField('parent');
        var parentList = this.form['parent_list'] || [];
        parentField.store.loadData( parentList );

        // выбрать первое значение, если есть
        if ( typeof this.form.template == 'undefined' && templateList.length )
            this.form.template = templateList[0].value;

        // установка значений
        form.setValues( this.form );

        // отобразить окно с формой
        this.show();

    }

});

/*Сохранение по ctrl+enter*/
var codes = [];
document.onkeydown = function(e) {
    e = e || window.event;

    if (e.keyCode==17) {
        codes.push(e.keyCode);
    }

    if (codes.length == 1 && codes[0] ==17 && e.keyCode==13) {
        document.getElementById('send_button').click();
        codes = [];
    }

};
document.onkeyup = function(e) {
    codes = [];
};
