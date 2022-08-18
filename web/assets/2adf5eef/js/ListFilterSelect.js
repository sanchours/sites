/**
 * Фильтр для спискового интерфейса автопостроителя
 */
Ext.define('Ext.Builder.ListFilterSelect', {
    extend: 'Ext.button.Button',
    fieldName: '',
    fieldValue: false,
    title: '',
    text: '',
    menu: {
        data: '',
        items: []
    },

    initComponent: function(){

        var self = this;

        if ( !self.menu ) {
            sk.error( 'ListFilterSelect has no items' );
            return;
        }

        // задать стандартный обработчик при нажатии
        self.menu.defaults = {
            handler: self.onFilterMenuClick
        };

        // найти выбранный элемент
        var selItem = self.findCheckedButtonElemet();

        // задать заголовок
        if ( selItem ) {
            self.text = self.title+': '+selItem.text;
            self.fieldValue = selItem.data;
        } else {
            self.text = self.title;
        }


        self.callParent();

    },

    /**
     * Находит выбранный элемент для кнопки-фильтра
     */
    findCheckedButtonElemet: function() {

        var self = this,
            items = self.menu.items,
            i, item
        ;

        // перебираем элементы
        for ( i in items ) {
            item = items[i];
            var data = (item.data) ? item.data : 'all';
            item.cls = 'sk-' + item.group + '-' + data;
            // возвращеем выбранный элемент, если его нашли
            if ( item.checked )
                return item;
        }

        // иначе возвращаем пкстое значение
        return items[0] ? items[0] : null;

    },

    /**
     * Событие при выборе элемента выпадающего списка
     * @param me
     */
    onFilterMenuClick: function(me){

        var button = me.up('panel').floatParent,
            itemText = me.text,
            itemValue = me.data
        ;

        // формирование текста надписи
        button.setText( button.title+': '+itemText );

        // сохранение выбранного значения
        button.fieldValue = itemValue;

        // выполнить фильтрацию (проброшенный метод)
        button.doSearch();

        return true;
    },

    /**
     * Возвращает имя фильтра
     */
    getFilterName: function(){
        return this.fieldName;
    },

    /**
     * Возвращает занчение фильтра
     */
    getFilterValue: function(){
        return this.fieldValue;
    }

});
