/**
 * Работа с параметрами дизайнерского режима
 */
Ext.define('Ext.Design.ParamEditor', {
    title: designLang.paramsPanelHeader,
    extend: 'Ext.Design.ParamEditorGrid',
    margins: '5 5 5 0',
    region: 'center',

    width: 300,
    nameColumnWidth: 200,

    propertyNames: {},
    actives: {},
    source: {},
    groupId: 0,

    // инициализация
    initComponent: function() {

        this.callParent();

        /**
         * установить обработчики
         */

            // выбор группы параметров
        processManager.addEventListener('group_select',this.path,'cmsOnGroupSelect');
        processManager.addEventListener('tree_loaded',this.path,'clearElements');
        processManager.addEventListener('reload_show_frame',this.path,this.reloadShowFrame);
        processManager.addEventListener('reload_param_editor',this.path,this.reloadItems);
        processManager.addEventListener('save_css_params',this.path,this.saveCssParams);
        processManager.addEventListener('reload_all',this.path,this.reloadAll);

        // изменение строки
        this.on('propertychange',this.onFieldUpdate,this);

    },

    // выполение обработки данных
    execute: function( data, cmd ){

        switch ( cmd ) {

            // загрузка подчиненных элементов
            case 'loadItems':
                var search_field = document.getElementById('search_field').getElementsByTagName('input')[0];
                search_field.value='';
            case 'findParam':

                this.groupId = data.groupId;
                this.canDelete = data.canDelete;

                // контейнер для элементов
                var items = {};

                // набор названий
                this.propertyNames = {};

                // набор редакторов
                this.customEditors = {};

                // если есть строки
                if ( data.items ) {

                    // перебрать все
                    for ( var key in data.items ) {

                        // строка с данными
                        var row = data.items[key];

                        // имя параметра
                        var name = row.id;

                        // добавить в набор
                        items[name] = row['value'];

                        // задать имя
                        this.propertyNames[name] = row.title;
                        this.actives[name] = row.active;

                        // устанавливает нужный редактор, если необходимо
                        this.setEditor( row );

                    }

                }

                // заменить набор элементов
                this.setSource( items );

                break;

        }

        this.setLoading( false );

        var store = this.getView().getStore();
        var nodes = this.getView().getNodes();

        Ext.Array.forEach(nodes, function(tr, i)
        {
            var record = store.getAt(i);

            if (record.data.value.length>40) {
                var el = Ext.get(tr);
                el.set({'data-qtip': record.data.value});
            }
        });

    },

    saveCssParams: function( items ) {
        var me = this;
        processManager.setData(me.path,{
            cmd: 'saveCssParams',
            data: items
        });
        processManager.postData();
    },

    reloadShowFrame: function() {

        var reload = processManager.getEventValue( 'get_auto_reload' );
        if ( typeof reload == 'undefined' )
            reload = true;

        if ( reload )
            frameApi.reloadDisplayFrame();

    },

    reloadItems: function() {
        this.cmsOnGroupSelect( this.groupId );
    },
    reloadAll: function() {
        location.reload();
    },
    /**
     * Вызывается при нажатии кнопки возврата значения по умолчанию
     * @param rec
     */
    onRevert: function( rec ) {

        var me = this;

        Ext.MessageBox.confirm(designLang.paramsRevertParamTitle, designLang.paramsRevertParam,function(res){

            if ( res !== 'yes' ) return false;

            processManager.setData(me.path,{
                cmd: 'revertParam',
                groupId: me.groupId,
                id: rec.name
            });
            processManager.postData();

            return true;

        });

    },
    onRemove: function( rec ) {
        var me = this;
        Ext.MessageBox.confirm(designLang.paramsRemoveParamTitle, designLang.paramsRemoveParam,function(res){
            if ( res !== 'yes' ) return false;
            processManager.setData(me.path,{
                cmd: 'removeParam',
                groupId: me.groupId,
                id: rec.name
            });
            processManager.postData();
            return true;
        });
    },

    /**
     * устанавливает нужный редактор, если необходимо
     * @param row - object
     */
    setEditor: function( row ) {

        var name = row['id'];

        // перебор типов
        switch ( row['type'] ) {

            // файл
            case 'url':
                this.customEditors[name] = Ext.create('Ext.sk.field.FileSelector',{
                    selectMode: 'designFileBrowser'
                });
                break;

            // цвет
            case 'color':
                this.customEditors[name] = Ext.create('Ext.sk.field.ColorSelector');
                break;

            // цвет
            case 'color_rgba':
                this.customEditors[name] = Ext.create('Ext.sk.field.ColorSelector', {
                    saveType: 'rgba'
                });
                break;

            // кнопка
            case 'button':
                this.customEditors[name] = Ext.create('Ext.sk.field.Button');
                break;

            case 'gallery':
                var params = {};
                params.only_show_album = 1;
                this.customEditors[name] = Ext.create('Ext.sk.field.GallerySelector',params);
                break;

            // Толщина шрифта
            case 'font-weight':
                this.customEditors[name] = this.createComboBox('bold, bolder, lighter, normal, 100, 200, 300, 400, 500, 600, 700, 800, 900');
                break;

            // Название шрифта
            case 'family':

                var defFonts = ['Tahoma', 'Arial', 'Verdana', 'Times New Roman'],
                    availableFonts = [],
                    dataItems = [],
                    data = [];

                if ( (typeof row['defvalue'] === 'undefined') || !row['defvalue'] ){
                    availableFonts = defFonts;
                } else {
                    availableFonts = defFonts.concat(row['defvalue']);
                }

                for ( var item in availableFonts ) {
                    dataItems.push({value: availableFonts[item]})
                }

                data = dataItems;

                this.customEditors[name] = this.createComboBox(data, true);
                break;

            // Стиль шрифта
            case 'font-style':
                this.customEditors[name] = this.createComboBox('normal, italic');
                break;

            // Повторение фонового изображения
            case 'repeat':
                this.customEditors[name] = this.createComboBox('repeat, no-repeat, repeat-x, repeat-y');
                break;

            // Позиция
            case 'position':
                this.customEditors[name] = this.createComboBox('left, right, center, top, bottom',true);
                break;

            // Горизонтальное выравнивание
            case 'h-position':
                this.customEditors[name] = this.createComboBox('left, right, center',true);
                break;

            // Вертикальное выравнивание
            case 'v-position':
                this.customEditors[name] = this.createComboBox('top, center, bottom',true);
                break;

            // Горизонтальное выравнивание
            case 'h-position-abs':
                this.customEditors[name] = this.createComboBox('left, right',true);
                break;

            // Вертикальное выравнивание
            case 'v-position-abs':
                this.customEditors[name] = this.createComboBox('top, bottom',true);
                break;

            // Вертикальное выравнивание
            case 'text-transform':
                this.customEditors[name] = this.createComboBox('none, capitalize, lowercase, uppercase, inherit',true);
                break;

            // Горизонтальное выравнивание
            case 'text-align':
                this.customEditors[name] = this.createComboBox('left, right, center, justify');
                break;

            // Вертикальное выравнивание
            case 'vectical':
            case 'vectical-align':
                this.customEditors[name] = this.createComboBox('baseline, sub, super, top, middle, bottom, text-top, text-bottom');
                break;

            // стиль бордюра
            case 'border-style':
                this.customEditors[name] = this.createComboBox('none, hidden, dotted, dashed, solid, double, groove, ridge, inset, outset, inherit');
                break;

            // подчёркивание
            case 'text-decoration':
                this.customEditors[name] = this.createComboBox('none, underline, overline, line-through, blink');
                break;

            // подчёркивание
            case 'switch':
                this.customEditors[name] = this.createComboBox('block, none, table-cell');
                break;

            case 'enable-selector':
                this.customEditors[name] = this.createComboBox('enabled, disabled');
                break;

            // Привязка фона
            case 'bg-attachment':
                this.customEditors[name] = this.createComboBox('scroll, fixed');
                break;

        }

    },

    /**
     * Добавляет выпадающий список как редактор
     * @param data
     * @param [editable]
     * @param [displayField]
     * @param [valueField]
     */
    createComboBox: function( data, editable, valueField, displayField ) {

        // значения по умолчанию
        if ( !editable ) editable = false;
        if ( !valueField ) valueField = 'value';
        if ( !displayField ) displayField = valueField;

        if ( typeof data === 'string' ) {
            var dataItems = [];
            var splitData = data.split(/,[\s]*/);
            for ( var item in splitData ) {
                dataItems.push({value: splitData[item]})
            }
            data = dataItems;
        }

        // набор элементов
        var fields = valueField===displayField ? [valueField] : [valueField, displayField];

        return Ext.create('Ext.form.field.ComboBox',{
            allowBlank: false,
            editable: editable,
            displayField: displayField,
            valueField: valueField,
            store:  {
                fields: fields,
                data: data
            },
            listeners: {
                select: function() {
                    this.triggerBlur();
                }
            }
        });

    },

    /**
     * При выборе группы (событие) в дереве добавить данные к посылке
     * @param groupId
     */
    cmsOnGroupSelect: function( groupId ){

        this.setLoading( true );

        // добавить данные в посылку
        processManager.setData(this.path,{
            cmd: 'loadItems',
            groupId: groupId
        })

    },

    /**
     * Обработчик изменения значения поля
     */
    onFieldUpdate: function(  source, recordName, value ){

        // собрать посылку
        processManager.setData(this.path,{
            cmd: 'updParam',
            value: value,
            id: recordName
        });

        // отослать её
        processManager.postData();

    },

    /**
     * Очистить набор элементов
     */
    clearElements: function(){

        // контейнер для элементов
        var items = {};

        // набор названий
        this.propertyNames = {};

        // набор редакторов
        this.customEditors = {};

        // заменить набор элементов
        this.setSource( items );

    }

});
