/**
 * Фильтр по дате для панели спискового интерфейса автопостроителя
 */
Ext.define('Ext.Builder.ListFilterDate', {
    extend: 'Ext.button.Button',
    title: '',
    text: '',
    fieldName: '',
    fn1: '',
    fn2: '',
    outFormat: '',
    // текущие значения фильтра
    fieldVal: {},
    // пришедшие из
    fieldValue: {},
    addParams: [],

    // инициализация компонента
    initComponent: function(){

        var button = this;

        button.fn1 = button.fieldName+'1';
        button.fn2 = button.fieldName+'2';

        var format = button.outFormat || 'Y-m-d';

        button.fieldVal = {};
        if ( button.fieldValue && button.fieldValue[0] )
            button.fieldVal[button.fn1] = Ext.Date.parse(button.fieldValue[0], format);
        if ( button.fieldValue && button.fieldValue[1] )
            button.fieldVal[button.fn2] = Ext.Date.parse(button.fieldValue[1], format);

        var dateMenu1 = Ext.create('Ext.menu.DatePicker', {
                fieldName: button.fn1,
                handler: button.onDateSelectHandler
            }),
            dateMenu2 = Ext.create('Ext.menu.DatePicker', {
                fieldName: button.fn2,
                handler: button.onDateSelectHandler
            })
        ;

        // формирование надписи
        button.rebuildText();

        // добавление компонента выбора даты
        button.menu = [{
            text: sk.dict('start'),
            iconCls: 'calendar',
            menu: dateMenu1
        },{
            text: sk.dict('end'),
            iconCls: 'calendar',
            menu: dateMenu2
        },{
            text: sk.dict('clear'),
            handler: button.onDateClear
        }];

        button.callParent();

    },

    /**
     * Событие при выборе даты
     * @param dp
     * @param date
     */
    onDateSelectHandler: function(dp, date){

        // инициализация переменных
        var menu = this.up(),
            button = menu.floatParent.floatParent
        ;

        // заполнение значений
        button.fieldVal[menu.fieldName] = date;

        // обновить текст кнопки
        button.rebuildText();

        // выполнение поиска
        button.doSearch();

    },

    /**
     * Событие при нажатии кнопки очистки фильтра дат
     */
    onDateClear: function(){

        //  находим компонент
        var button = this.up().floatParent;

        // очищаем установленные значения
        button.fieldVal = {};

        // задаем пустой заголовок
        button.setText( button.title );

        // выполняем поиск
        button.doSearch();

    },

    /**
     * Перестраивает текст кнопки
     */
    rebuildText: function(){

        var button = this,
            val1 = button.fieldVal[button.fn1],
            val2 = button.fieldVal[button.fn2],
            hasVal = (val1 || val2),
            format = Ext.util.Format.dateFormat
        ;

        val1 = val1 ? Ext.Date.format(val1, format) : '*';
        val2 = val2 ? Ext.Date.format(val2, format) : '*';

        if ( hasVal ) {
            button.setText( button.title+': '+val1+' - '+val2 );
        } else {
            button.setText( button.title );
        }

    },

    /**
     * Установка нескольких значений для фильтрации одновременно
     * @param fnc функция установки значений фильтрации
     *      name имя значения для фильтрации
     *      val значение параметра
     */
    getGroupFilter: function( fnc ){

        // выбрать значения
        var button = this,
            val1 = button.fieldVal[button.fn1],
            val2 = button.fieldVal[button.fn2],
            format = button.outFormat || 'Y-m-d'
        ;

        // отформатировать значения
        val1 = val1 ? Ext.Date.format(val1, format) : '';
        val2 = val2 ? Ext.Date.format(val2, format) : '';

        // задать параметры фильтра
        fnc(button.fn1,val1);
        fnc(button.fn2,val2);

    }

});
