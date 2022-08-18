/**
 * Панель выбора языка
 */
Ext.define('Ext.Cms.Lang',{
    extend: 'Ext.container.AbstractContainer',
    border: 0,
    margin: '0 0 3 0',
    padding: 0,

    currentLang: '',
    langList: [],

    //шаблонные данные для рендеринга
    renderData: {
        username: '---',
        lastlogin: '---'
    },

    childEls: ['body'],

    // шаблон для вывода
    renderTpl: [
        '<div class="language">',
            '<div id="{id}-body"></div>',
        '</div>'
    ],

    getTargetEl: function() {
        return this.body || this.frameBody || this.el;
    },

    initComponent:function(){

        var me = this;
        var langName, langRow;
        var langList = [];

        me.callParent();

        // сборка списка языков
        for ( langName in this.langList ) {
            langRow = this.langList[langName];
            langList.push( langRow );
        }

        // сборка хранилища языков
        var langListStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'title'],
            data : langList
        });

        // добавление компонента выбора в вывод
        var langComponent = Ext.create('Ext.form.ComboBox', {

            width: 100,

            fieldLabel: '',
            editable: false,
            store: langListStore,
            displayField: 'title',
            valueField: 'name',
            value: sk.lang,

            listeners: {
                change: function( self, name ) {
                    processManager.sendDataFromMainContainer( me, {
                        cmd: 'setLang',
                        lang: name
                    } );
                }
            }

        });

        me.add( langComponent );

    },

    execute: function( data, cmd ) {

        if ( data.error )
            sk.error( data.error );

    }

});
