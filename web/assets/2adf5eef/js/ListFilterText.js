//noinspection JSUnusedGlobalSymbols
/**
 * Поле для текстового фильтра
 *
 * За основу взят компонент Ext.ux.form.SearchField
 * Не устроило меня в оригинале то, что можно было работать только с внешним хранилищем,
 *      а использовать как самомтоятельное поле не получалось
 * Изменены методы onTrigger1Click, onTrigger2Click и добавлен doSearch
 *
 */
Ext.define('Ext.Builder.ListFilterText', {
    extend: 'Ext.form.field.Trigger',

    trigger1Cls: Ext.baseCSSPrefix + 'form-clear-trigger',

    trigger2Cls: Ext.baseCSSPrefix + 'form-search-trigger',

    width: 200,

    hasSearch : false,
    fieldName: '',
    fieldValue: '',

    initComponent: function(){
        this.callParent(arguments);
        this.setValue(this.fieldValue);
        this.on('specialkey', function(f, e){
            if(e.getKey() == e.ENTER){
                this.onTrigger2Click();
            }
        }, this);
    },

    afterRender: function(){
        this.callParent();
        if ( !this.fieldValue )
            this.triggerEl.item(0).setDisplayed( 'none');
    },

    onTrigger1Click : function(){
        var me = this;
        me.setValue('');
        me.hasSearch = false;
        me.triggerEl.item(0).setDisplayed('none');
        me.doComponentLayout();
        me.doSearch();
    },

    onTrigger2Click : function(){
        var me = this;
        me.hasSearch = true;
        me.triggerEl.item(0).setDisplayed('block');
        me.doComponentLayout();
        me.doSearch();
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
        return this.getValue();
    },

    doSearch: function(){
        return false;
    }

});
