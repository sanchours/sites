/**
 * Специфическое поле типа набор галочек
 *
 */

Ext.define('Ext.Tool.CheckSet4Module',{
    extend: 'Ext.form.FieldSet',
    defaultType: 'checkbox',
    layout: 'anchor',
    items: [],
    fieldNames: [],

    initComponent: function(){

        var me = this,
            item,itemId,
            newItems,
            newItem,
            fieldName = me.getFieldName()
            ;

        const delimiter = '_';

        newItems = [];

        // перебор элементов в группе
        for ( itemId in me['items'] ) {

            // инициализация элемента
            item = me['items'][itemId];

            // составление описания для элемента
            newItem = item;
            newItem.xtype = 'checkbox';
            newItem.name = fieldName + delimiter + item.name;
            newItem.boxLabel = item.title || item.name;
            newItem.checked = Boolean(parseInt(item.value));
            newItem.uncheckedValue = 0;
            newItem.inputValue = 1;

            if (typeof item.execute == 'undefined') {
                newItem.cls = 'module_checker';
                newItem.listeners = {
                    render: function (component) {
                        component.getEl().on('click', function(e) {
                            setAllData();
                        })
                    }
                }
            } else {
                newItem.cls = 'set_all';
                newItem.listeners  = {
                    render: function(component) {
                        component.getEl().on('click', function(e) {
                            var value = component.value;
                            var elements = Ext.select('.module_checker');
                            for (var i = 0; i < elements.elements.length; i++) {
                                var cmp = Ext.getCmp(elements.elements[i].id);
                                cmp.setValue(value);

                            }
                        })
                    }
                }
            }

            // добавление имени поля в список
            me.fieldNames.push( newItem.name );

            newItems.push( newItem );

        }

        me.items = newItems;

        me.callParent();

    },

    execute: function(){

        setAllData();
    },
    getFieldName: function() {
        return this.name;
    }

});

function setAllData(){

    var elements = Ext.select('.set_all');

    var set_all = Ext.getCmp(elements.elements[0].id);

    var elements2 = Ext.select('.module_checker');

    /*Обходим все чекбоксы и проверяем все ли они выставлены/невыставлены*/
    var checked = set_all.getValue();
    var all_flag = true;
    for (var i = 0; i < elements2.elements.length; i++) {
        var cmp = Ext.getCmp(elements2.elements[i].id);
        if (cmp.getValue() != true){
            all_flag = false;
        }
    }

    /*если не все чекбоксы имеют одинаковое значение*/
    if (!all_flag){
        set_all.setValue(false);
    } else {
        set_all.setValue(true);
    }
}
