/**
 * Специфическое поле типа набор галочек
 *
 * На вход получает массив групп с подчиненными элементами
 *  [
 *      delimiter: '.', - опционально. по умолчанию '.'
 *      groups: {
 *          name: 'group1',
 *          title: 'Группа 1',
 *          items: [
 *              {
 *                  name: 'param1',
 *                  title: 'Параметр 1',
 *                  value: 1,
 *
 *                  // -- то, что ниже только в планах, но работать будет так, когда понадобится
 *                  type: 'check'/'string'/'select', - опционально. по умолчанию check
 *                  items: [{v:'value',t:'title'}] - опционально. нужно при type=select
 *              },
 *              ...
 *          ]
 *      },
 *      ...
 *  ]
 *
 * Отдает объект в общий набор значений поля с составными именами {paramName}.{groupName}.{fieldName}
 *
 */

Ext.define('Ext.Tool.CheckSet',{
    extend: 'Ext.form.FieldSet',
    defaultType: 'checkbox',
    layout: 'anchor',
    items: [],
    fieldNames: [],

    initComponent: function(){

        var me = this,
            values = [],
            group,groupId,
            item,itemId,
            newItems,
            newGroup,
            newItem,
            fieldName = me.getFieldName()
        ;

        const delimiter = '_';

        // перебор пришадших групп
        for ( groupId in me['value']['groups'] ) {

            // входная группа
            group = me['value']['groups'][groupId];

            // составление описания для группы
            newGroup = group;
            newGroup.xtype = 'fieldset';
            newItems = [];

            // перебор элементов в группе
            for ( itemId in group['items'] ) {

                // инициализация элемента
                item = group['items'][itemId];

                // составление описания для элемента
                newItem = item;
                newItem.xtype = 'checkbox';
                newItem.name = fieldName + delimiter + group.name + delimiter + item.name;
                newItem.boxLabel = item.title || item.name;
                newItem.checked = Boolean(parseInt(item.value));
                newItem.uncheckedValue = 0;
                newItem.inputValue = 1;

                // добавление имени поля в список
                me.fieldNames.push( newItem.name );

                newItems.push( newItem );

            }

            newGroup.items = newItems;
            values.push( newGroup );

        }

        me.items = values;

        me.callParent();

    },

    getFieldName: function() {
        return this.name;
    }

});
