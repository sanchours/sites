//noinspection AnonymousFunctionJS
/**
 * Типы полей редакторов
 */

Ext.define('Ext.sk.FieldTypes',{
    types: {
        hide: {
            type_id: 0,
            xtype: 'hiddenfield',
            hideInContent: true
        },
        str: {
            type_id: 1,
            xtype: 'textfield',
            rendererList: {
                link: function(value,meta,record,rowIndex,colIndex){

                    // собрать данные
                    //noinspection JSUnresolvedVariable
                    var col = this.columns[colIndex],
                        linkHrefTpl = col['linkHrefTpl'] || '',
                        linkTitleTpl = col['linkTitleTpl'] || '',
                        linkBlank = col['linkBlank'] || '',
                        href = sk.parseTpl( linkHrefTpl, record.data ),
                        title = sk.parseTpl( linkTitleTpl, record.data )
                    ;

                    // нет ссылки - просто отдать значение
                    if ( !href ) return value;

                    // собрать строку
                    return Ext.String.format(
                        '<a href="{0}"{2}>{1}</a>',
                        href,
                        title,
                        linkBlank?'target="_blank"':''
                    );

                }
            },
            listEditableSettings: {
                editor: {
                    xtype: 'textfield'
                }
            }
        },
        text: {
            type_id: 2,
            xtype: 'textareafield',
            baseSettings: {
                margin: '0 5 5 0',
                labelAlign: 'top'
            },
            listEditableSettings: {
                editor: {
                    xtype: 'textfield'
                }
            }
        },
        text_html:{
            type_id: 31,
            xtype: 'textareafield',
            baseSettings: {
                margin: '0 5 5 0',
                labelAlign: 'top',
                mirror: null,
                onRender: function(ct, position) {

                    Ext.form.TextArea.superclass.onRender.call(this, ct, position);

                    //noinspection JSUnresolvedVariable
                    this.mirror = CodeMirror.fromTextArea(this.el.dom.getElementsByTagName("textarea")[0], {
                        lineNumbers: true,
                        mode: "text/html",
                        matchBrackets: true,
                        profile: 'xhtml',
                        extraKeys: {
                            //Tab: function(cm) { cm.replaceSelection("    ", "end"); },
                            "Ctrl-Space": "autocomplete",
                            "F11": function(cm) {
                                cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                            },
                            "Esc": function(cm) {
                                if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                            }

                        }
                    });
                    //noinspection JSUnresolvedVariable
                    this.mirror.setSize('100%',this.height-20);

                },
                getRawValue:function () {
                    if (this.mirror)
                        return this.mirror.getValue();
                    else return '';
                }

            },
            listEditableSettings: {
                editor: {
                    xtype: 'textfield'
                }
            }
        },
        text_js: {
            type_id: 32,
            xtype: 'textareafield',
            baseSettings: {
                margin: '0 5 5 0',
                labelAlign: 'top',
                mirror: null,
                onRender: function(ct, position) {
                    Ext.form.TextArea.superclass.onRender.call(this, ct, position);

                    //noinspection JSUnresolvedVariable
                    this.mirror = CodeMirror.fromTextArea(this.el.dom.getElementsByTagName("textarea")[0], {
                        lineNumbers: true,
                        mode: {
                            name: "javascript",
                            globalVars: true},
                        matchBrackets: true,
                        globalVars: true,
                        extraKeys: {
                            Tab: function(cm) { cm.replaceSelection("    ", "end"); },
                            "Ctrl-Space": "autocomplete",
                            "F11": function(cm) {
                                cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                            },
                            "Esc": function(cm) {
                                if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                            }

                        }
                    });
                    //noinspection JSUnresolvedVariable
                    this.mirror.setSize('100%',this.height-20);
                },
                getRawValue:function () {
                    if (this.mirror)
                        return this.mirror.getValue();
                    else return '';
                }

            },
            listEditableSettings: {
                editor: {
                    xtype: 'textfield'
                }
            }
        },
        text_css: {
            type_id: 33,
            xtype: 'textareafield',
            baseSettings: {
                margin: '0 5 5 0',
                labelAlign: 'top',
                mirror: null,
                onRender: function(ct, position) {
                    Ext.form.TextArea.superclass.onRender.call(this, ct, position);

                    //noinspection JSUnresolvedVariable
                    this.mirror = CodeMirror.fromTextArea(this.el.dom.getElementsByTagName("textarea")[0], {
                        lineNumbers: true,
                        //mode: "text/html",
                        mode: "css",
                        matchBrackets: true,
                        profile: 'xhtml',
                        styleActiveLine: true,
                        extraKeys: {
                            Tab: function(cm) { cm.replaceSelection("    ", "end"); },
                            "Ctrl-Space": "autocomplete",
                            "F11": function(cm) {
                                cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                            },
                            "Esc": function(cm) {
                                if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                            }

                        }
                    });
                    if (this.heightInPanel){
                        if (this.height){
                            var height = this.height;
                        } else {
                            var height = this.findParentByType('panel').getHeight() - 3;
                        }
                        this.mirror.getTextArea().height = height + 'px';
                        this.mirror.setSize('100%', height);
                    }else{
                        //noinspection JSUnresolvedVariable
                        this.mirror.setSize('100%', this.height - 18)
                    }

                },
                getRawValue:function () {
                    if (this.mirror)
                        return this.mirror.getValue();
                    else return '';
                }

            },
            listEditableSettings: {
                editor: {
                    xtype: 'textfield'
                }
            }
        },
        wyswyg: {
            type_id: 3,
            xtype: 'ckeditor',
            baseSettings: {
                labelAlign: 'top',
                margin: '0 5 30 0',
                height: 300
            }
        },
        imagefile: {
            type_id: 4,
            xtype: 'selectfilefield'
        },
        check: {
            type_id: 5,
            xtype: 'checkboxfield',
            baseSettings: {
                uncheckedValue: 0,
                inputValue: 1
            },
            listSettings: {
                renderer: function(value,meta,record,rowIndex,colIndex){
                    var layer = this.up();
                    if (layer.items.items[0].columns[colIndex].showAsDisabledEdit)
                        return (value && value!=='0') ? '<div class="x-grid-checkheader x-grid-cb-disable-checked">&nbsp;</div>' : '<div class="x-grid-checkheader x-grid-cb-disable">&nbsp;</div>';
                    else
                        return (value && value!=='0') ? '+' : '';
                },
                align: 'center'
            },
            listEditableSettings: {
                xtype: 'checkcolumn',
                editor: {
                    xtype: 'checkbox',
                    cls: 'x-grid-checkheader-editor'
                },
                listeners: {
                    checkchange: function( self, index ) {
                        processManager.getMainContainer(self).setLoading(true);
                        processManager.sendDataFromMainContainer( self, {
                            cmd: self['listSaveCmd'],
                            from: 'field',
                            data: self.up('gridpanel').getStore().getAt( index ).data,
                            field_name: self.dataIndex
                        } );
                    }
                },
                align: 'center'
            },
            updField: function( field ){
                if ( field.value && field.value!=='0' )
                    field.checked = true;
                return field;
            }
        },
        file: {
            type_id: 6,
            xtype: 'selectfilefield'
        },
        html: {
            type_id: 7,
            xtype: 'htmleditor',
            baseSettings: {
                labelAlign: 'top',
                height: 250
            }
        },
        show: {
            type_id: 8,
            xtype: 'displayfield',
            baseSettings: {
                fieldBodyCls: 'builder-show-field'
            }
        },
        select: {
            type_id: 9,
            xtype: 'combo',
            baseSettings: {
                mode: 'local',
                triggerAction: 'all',
                forceSelection: true,
                allowBlank: false,
                editable: false,
                displayField: 't',
                valueField:'v',
                queryMode: 'local',
                saveKeys: 'ctrl_enter',
                store:  {
                    fields: ['v', 't'],
                    data: []
                },
                isDirty : function() {
                    var me = this;
                    return !me.disabled && !me.isEqualAsString(me.getValue(), me.originalValue);
                }

            }
        },
        num: {
            type_id: 10,
            xtype: 'numberfield',
            listEditableSettings: {
                editor: {
                    xtype: 'numberfield',
                    allowDecimals: false
                }
            }
        },
        float: {
            type_id: 11,
            xtype: 'numberfield',
            baseSettings: {
                allowDecimals: true,
                decimalSeparator: '.',
                decimalPrecision: 10
            },
            listEditableSettings: {
                editor: {
                    xtype: 'numberfield'
                }
            }
        },
        money: {
            type_id: 12,
            xtype: 'numberfield',
            baseSettings: {
                allowDecimals: true,
                decimalSeparator: '.',
                decimalPrecision: 2,
                minValue: 0
            },
            listEditableSettings: {
                editor: {
                    xtype: 'numberfield',
                    minValue: 0
                }
            }
        },
        date: {
            type_id: 15,
            xtype: 'datefield',
            baseSettings: {
                format: 'd.m.Y',
                submitFormat: 'Y-m-d'
            },
            updField: function( field ){
                if ( !parseInt(field.value) )
                    field.value = '';
                return field;

            }
        },
        datetime: {
            type_id: 17,
            xtype: 'datetimefield',
            requires: ['Ext.ux.form.field.DateTime'],
            baseSettings: {
                dateFormat: 'd.m.Y',
                dateSubmitFormat: 'Y-m-d',
                timeFormat: 'H:i',
                timeAltFormats: 'H:i|H:i:s',
                timeSubmitFormat: 'H:i:s'
            },
            updField: function( field ){
                if ( !parseInt(field.value) )
                    field.value = '';
                return field;

            }
        },
        time: {
            type_id: 16,
            xtype: 'timefield',
            baseSettings: {
                format: 'H:i',
                altFormats: 'H:i|H:i:s',
                submitFormat: 'H:i:s',
                saveKeys: 'ctrl_enter'
            }

        },
        inherit: {
            type_id: 20,
            hideInContent: true,
            xtype: 'textareafield'
        },
        specific: {
            updField: function( field, moduleLayer ){

                var extendClassName;
                if ( field.layerName )
                    moduleLayer = field.layerName;
                else if ( !moduleLayer )
                    moduleLayer = buildConfig.layerName;

                if ( field['extendLibName'] ) {
                    extendClassName = 'Ext.'+moduleLayer+'.'+field['extendLibName'];
                    return Ext.create(extendClassName,field);
                } else {
                    return field;
                }

            }
        },
        pass: {
            xtype: 'textfield',
            baseSettings: {
                inputType: 'password'
            }
        },
        button: {
            type_id: 21,
            xtype: 'selectfilefield'
        },
        gallery: {
            type_id: 22,
            xtype: 'selectgalleryfield'
        },
        multiselect: {
            type_id: 23,
            xtype: 'multiselectfield',
            multiSelect: true,
            baseSettings: {
                store:  {
                    fields: ['value'],
                    data: []
                },
                isDirty : function() {
                    var me = this;
                    return !me.disabled && !me.isEqualAsString(me.getValue(), me.originalValue);
                }
            },
            updField: function( field ){

                if ( field.value && typeof field.value === 'string' )
                    field.value = field.value.split(',');

                return field;

            }

        },
        colorselector: {
            type_id: 24,
            xtype: 'colorfield'
        },
        mapSingleMarker: {
            type_id: 25,
            xtype: 'mapSingleMarker'
        },
        mapListMarkers: {
            type_id: 26,
            xtype: 'mapListMarkers'
        },
        paymentObject:
            {
                type_id: 27,
                xtype: 'combo',
                baseSettings: {
                    mode: 'local',
                    triggerAction: 'all',
                    forceSelection: true,
                    allowBlank: false,
                    editable: false,
                    displayField: 't',
                    valueField: 'v',
                    queryMode: 'local',
                    saveKeys: 'ctrl_enter',
                    store: {
                        fields: ['v', 't'],
                        data: []
                    },
                    isDirty: function () {
                        var me = this;
                        return !me.disabled && !me.isEqualAsString(me.getValue(), me.originalValue);
                    }

                }
            },
        selectimage: {
            type_id: 28,
            xtype: 'combo',
            baseSettings: {
                mode: 'local',
                triggerAction: 'all',
                forceSelection: true,
                allowBlank: false,
                editable: false,
                displayField: 't',
                valueField:'v',
                queryMode: 'local',
                saveKeys: 'ctrl_enter',
                store:  {
                    fields: ['v', 't'],
                    data: []
                },
                isDirty : function() {
                    var me = this;
                    return !me.disabled && !me.isEqualAsString(me.getValue(), me.originalValue);
                }

            }
        },
        multiselect: {
            type_id: 29,
            xtype: 'multiselectfield',
            multiSelect: true,
            baseSettings: {
                store:  {
                    fields: ['value'],
                    data: []
                },
                isDirty : function() {
                    var me = this;
                    return !me.disabled && !me.isEqualAsString(me.getValue(), me.originalValue);
                }
            },
            updField: function( field ){

                if ( field.value && typeof field.value === 'string' )
                    field.value = field.value.split(',');

                return field;

            }

        }
    },

    // получить набор типов в виде объекта
    getTypesAsObject: function(){
        return this.types;
    },

    /**
     * Формирование набора полей для формы по инициализационному массиву
     * @param items
     * @param layerName
     */
    createFields: function( items, layerName ) {

        var me = this,
            outItems = [],
            field,
            fieldKey,
            fieldDef,
            attrName,
            cmsXTypes = me.getTypesAsObject(),
            mType
        ;

        // добавление полей
        for ( fieldKey in items ) {

            // описание поля
            fieldDef = items[fieldKey];

            if ( fieldDef['customField'] ) {
                // добавить поле в форму
                var layer = (typeof fieldDef['layer'] !== 'undefined') ? fieldDef['layer'] :layerName;
                outItems.push( Ext.create( 'Ext.'+layer+'.'+fieldDef['customField'], fieldDef ) );

                continue;
            }

            // проверка на существование типа поля
            if ( typeof cmsXTypes[fieldDef.type] === 'undefined' )
                throw 'Unknown field type ['+fieldDef.type+']';

            // описание поля
            mType = cmsXTypes[fieldDef.type];

            // описание для ExtJS
            field = {
                name: fieldDef.name,
                fieldLabel: fieldDef.title,
                value: fieldDef.value
            };

            if ( mType['requires'] ) {
                field.requires = mType['requires'];
                for ( var key in mType['requires'] ) {
                    //sk.loadJS( mType['requires'][key] );
                    Ext.create(mType['requires'][key]);
                }
                //console.log( mType['requires'][key] );
            }


            // дополнение незаполненными полями
            for (attrName in fieldDef) {
                if ( typeof field[attrName] === 'undefined' )
                    field[attrName] = fieldDef[attrName];
            }

            // дополнение базовыми настройками
            if ( typeof mType === 'object' && mType.baseSettings ) {
                for (attrName in mType.baseSettings) {
                    if ( typeof field[attrName] === 'undefined' )
                        field[attrName] = mType.baseSettings[attrName];
                }
            }

            // тип поля
            if ( typeof mType === 'object' ) {

                // тип
                if ( mType.xtype )
                    field.xtype = mType.xtype;

                // модификация
                if ( mType.updField )
                    field = mType.updField( field, layerName );

            } else {
                if ( !field.xtype )
                    field.xtype = mType;
            }

            // обработка шажатия "Enter"
            if ( !field.listeners )
                field.listeners = [];
            field.listeners['specialkey'] = this.handlerFieldSpecialKey;


            // добавить поле в форму
            outItems.push( field );

            if ( fieldDef['subtext'] ) {

                outItems.push( {
                    xtype: 'displayfield',
                    baseCls: 'form-field-subtext',
                    fieldLabel: '',
                    hideEmptyLabel: false,
                    value: fieldDef['subtext'],
                    groupTitle: fieldDef['groupTitle']
                } );

            }

        }

        return me.toGroups(outItems);

    },

    /**
     * Обработчик нажатия спец кнопок на полях
     * @param field
     * @param e
     */
    handlerFieldSpecialKey: function(field, e){

        var form;
        var saveKeys = field.saveKeys || 'enter';

        switch ( saveKeys ) {

            case 'enter':
                if ( e.getKey() == e.ENTER) {
                    form = field.up('form');
                    if ( form && form.callSaveState )
                        form.callSaveState();
                }
                break;

            case 'ctrl_enter':
                if ( e.ctrlKey && e.getKey() == e.ENTER) {
                    form = field.up('form');
                    if ( form && form.callSaveState )
                        form.callSaveState();
                }
                break;

            default:
                break;

        }

    },

    /**
     * Расклажывает элементы по группам, если это требуется
     * @param itemList
     * @returns {*}
     */
    toGroups: function( itemList ) {

        var item, key;
        var groups = {};
        var out = [];

        for ( key in itemList ) {

            item = itemList[key];
            groupTitle = item['groupTitle'];

            if ( groupTitle ) {

                // Добавка приставки к числовым названиям групп, что бы js не отсортировал их автоматически
                var groupIndex = 'g' + groupTitle;

                if (groups[groupIndex] == undefined)
                    groups[groupIndex] = {
                        xtype: 'fieldset',
                        title: groupTitle,
                        padding: '0 5 5 5',
                        margin: '5 5 2 0',
                        items: []
                    };

                groups[groupIndex].items.push(item);

                /* Добавление кнопки сворачивания группы, если хотя бы у одного поля определён параметр groupType */
                // Показ кнопки сворачивания группы
                groups[groupIndex].collapsible = item['groupType'];
                // Состояние группы. True = свёрнута
                groups[groupIndex].collapsed = (item['groupType'] > 1);

            } else
                out.push(item);
        }

        for ( key in groups )
            out.push( groups[key] );

        return out;

    }


});
