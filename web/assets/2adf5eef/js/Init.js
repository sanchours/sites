/**
 * Основной функциональный набор CMS
 */

// инициализация конфигурационных констант
Ext.Loader.setPath('Ext.ux', extJsDir+'/js/ux');
processManager = Ext.create('Ext.sk.ProcessManager');
pageHistory = Ext.create('Ext.sk.History');
Ext.tip.QuickTipManager.init();

//noinspection JSUnresolvedVariable
Ext.define('Ext.sk.Init', {

    path: 'init',
    layerName: 'sk',
    extend: 'Ext.Component',
    require: [ 'Ext.sk.ProcessManager' ],
    moduleName: 'Init',
    lang: lang,

    /**
     * вывод всплывающего сообщения а админке
     * @param t1 - заголовок
     * @param t2 - основной текст
     * @param addCls
     * @param time
     */
    message: function(t1, t2, addCls,time) {
        var mes = Ext.create('Ext.sk.Messager');
        mes.msg(t1, t2, addCls, time);
    },

    /**
     * Вывод всплывающего сообщения с тектом ошибки
     * @param header заголовк плашки, если задан один - используется как основной текст
     * @param text
     * @param delay
     */
    error: function( header, text, delay ) {

        // выдать ошибку
        this.showError( header, text, delay );

        // инициировать событие ошибки
        processManager.fireEvent( 'error', text ? text : header );

        // отослать данные, если есть
        processManager.postDataIfExists();

    },

    // набор вызовов подтверждений в очереди
    confirm_list: [],

    // набор вызовов функций
    confirm_calls: [],

    /**
     * Системный вывод подтверждений
     * @param headText
     * @param text
     * @param fn
     * @param scope
     */
    confirmWindow: function( headText, text, fn, scope ) {

        var me = this;

        Ext.MessageBox.onEsc = function() {
            if ( this.cfg.callback )
                this.cfg.callback('no',undefined,this.cfg.scope);
            this.hide();
            return true;
        };

        Ext.MessageBox.close = Ext.MessageBox.onEsc;

        if ( Ext.MessageBox.isVisible() ) {
            me.confirm_list.push({
                headText: headText,
                text: text,
                fn: fn,
                scope: scope
            });
            return;
        }

        Ext.MessageBox.confirm( headText, text, function( res ) {

            me.confirm_calls.push( {
                fn: fn,
                arguments: arguments,
                scope: scope
            } );

            if ( res === 'no' ) {
                me.confirm_callback( res );
                return;
            }

            if ( me.confirm_list.length ) {
                var a = me.confirm_list.shift();
                sk.confirmWindow( a.headText, a.text, a.fn, a.scope);
            } else {
                me.confirm_callback();
            }

        }, scope);

    },
    warningWindow: function( headText, text ) {
        Ext.MessageBox.confirm({
            title: headText,
            msg: text,
            icon: 'ext-mb-warning',
            buttonText: {
                ok: 'Ок'
            },
            buttons: 1,
            callback: function(res){
                if ( res !== 'yes' ) return;
            }
        });
    },



    /**
     * Вызывает набор действий для вызова после всех подтверждений
     * @protected
     */
    confirm_callback: function( res ) {

        var me = this;
        var key, item;

        for ( key in me.confirm_calls ) {
            item = me.confirm_calls[key];
            if ( typeof res !== 'undefined' )
                item.arguments[0] = res;
            item.fn.apply( item.scope, item.arguments );
        }

        me.confirm_list_clear();

    },

    /**
     * Очищает набор подтверждений
     * @protected
     */
    confirm_list_clear: function() {
        this.confirm_list = [];
        this.confirm_calls = [];
    },

    /**
     * Задает обзазку CKEditor-а для элемента
     * @param item_id
     * @param [options]
     */
    initCKEditorOnPlace: function( item_id, options ) {
        options = options || {};
        options.language = buildConfig.CKEditorLang;

        CKEDITOR.lang.load(options.language, 'en', function(){
            if (typeof CKEDITOR.lang[String(options.language)] != 'undefined'){
                CKEDITOR.lang[String(options.language)] = Ext.merge({},
                    CKEDITOR.lang[String(options.language)],
                    options.addLangParams
                )
            }
        });

        return CKEDITOR.inline( item_id, options );
    },

    /**
     * Удаляет обвязку CKEditor для элемента
     * @param item_id
     */
    removeCKEditorOnPlace: function( item_id ) {

        if( Ext.query('#'+item_id) )
            CKEDITOR.instances[item_id].destroy();
    },

    /**
     * Вывод данных в консоль логирования
     * @param text
     */
    log: function( text ){
        processManager.fireEvent('log',text);
    },

    /**
     * Выдать сообщение об ошибке
     */
    showError: function( header, text, delay ) {

        if ( !text ) {
            text = header;
            header = sk.dict('error');
        }

        if ( !delay )
            delay = 5000;

        this.message(header, text, 'msg-error', delay);
    },

    // инициализация
    initComponent: function() {

        this.callParent();

        processManager.addEventListener( 'reload', this.path, this.reloadPage, this );

        // пустая посылка - запуск инициализации
        processManager.postData();

    },

    /**
     * Отдает значение из словаря
     * @param {string} name
     * @returns {string}
     */
    dict: function( name ) {
        //noinspection JSUnresolvedVariable
        return dict[name] || '{'+name+'}';
    },

    reloadPage: function() {
        window.location.reload();
    },

    // открыть новое подчиненное окно
    newWindow: function (href, inData) {
        var data = Ext.merge({
            width: '80%',
            height: '70%'
        }, inData);

        var w = data.width;
        var h = data.height;

        if(typeof w=='string' && w.length>1 && w.substr(w.length-1,1)=='%')
            w=parseInt(window.screen.width*parseInt(w,10)/100,10);
        if(typeof h=='string' && h.length>1 && h.substr(h.length-1,1)=='%')
            h=parseInt(window.screen.height*parseInt(h,10)/100,10);

        var top = (window.screen.height - h) / 2;
        var left = (window.screen.width - w) / 2;

        var newWindow = window.open(href, 'sk_popup_window', "location=no, menubar=no, scrollbars=1, toolbar=no, status = no, resizable=no, directories=no, width=" + w + ",left=" + left + ", height=" + h + ", top=" + top);
        newWindow.focus();

        return true;
    },

    /**
     * Список уже подгруженных css файлов
     */
    loadedCssFiles: [],

    /**
     * Подгружает CSS файл
     * @param fileName
     */
    loadCss: function( fileName ) {

        // проверить наличие имени
        if ( !fileName ) return false;

        // завершить, если такой файл уже подключен
        if ( Ext.Array.indexOf( this.loadedCssFiles, fileName) !== -1 )
            return true;

        this.loadedCssFiles.push( fileName );

        // вставить в DOM для вызова и обработки
        var fileRef=document.createElement("link");
        fileRef.setAttribute("rel", "stylesheet");
        fileRef.setAttribute("type", "text/css");
        fileRef.setAttribute("href", fileName) ;
        document.getElementsByTagName("head")[0].appendChild(fileRef);

        return true;

    },

    loadedJSFiles: [],

    /**
     * Подгружает JS файл
     * @param fileName
     */
    loadJS: function( fileName ) {

        // проверить наличие имени
        if ( !fileName ) return false;

        // завершить, если такой файл уже подключен
        if ( this.loadedJSFiles.indexOf(fileName) !== -1 )
            return true;

        this.loadedJSFiles.push( fileName );

        Ext.Loader.loadScriptFile(
            fileName,
            Ext.emptyFn,
            function() {
                sk.error( 'Ошибка загрузки файла ['+fileName+']' )
            },
            window,
            true
        );

        return true;

    },

    /**
     * определяет есть ли в массиве значение
     * @param val mixed искомое значение
     * @param arr Array массив
     */
    inArray: function( val, arr ) {
        for ( var key in arr ) {
            if ( val == arr[key] )
                return true;
        }
        return false;
    },

    /**
     * Парсинг шаблона Ext.Template
     * @param tpl mixed описание массива
     * @param data array/object данные для парсинга
     */
    parseTpl: function( tpl, data ){
        var oTpl = new Ext.Template(tpl);
        return oTpl.applyTemplate(data);
    }

});
