//noinspection JSUnusedGlobalSymbols
/**
 * JS менеджер для агрегации и проксирования запросов, а также универсализации совместной работы
 *      множества js объектов и их серверных частей друг с другом и между собой
 */

Ext.define( 'Ext.sk.ProcessManager', {

    // адрес для отправки запросов
    url: buildConfig.request_script,

    // очередь событий
    eventQuery: {},

    // счетчик подчиненных вызовов событий
    eventCnt: 0,

    // ограничитель рекурсивных входов
    eventMaxCnt: 5,

    // набор связей путь-контейнер(id)
    containerByPath: {},

    // набор посылок серверу
    bufferPack: {},

    // набор данных о текущем положении
    locPack: {},

    // счетчик для создания уникальных id
    uniqueIdCounter: 0,

    // флаг сброса таймаута при следующей отправке запроса
    doNotUseTimeoutFlag: false,

    /**
     * Получить уникальное число в рамках сессии
     */
    getUniqueNum: function() {
        return ++this.uniqueIdCounter;
    },

    /**
     * Получить уникальный идентификатор
     */
    getUniqueId: function(){
        var cnt = this.getUniqueNum();
        return 'pm_id_'+cnt.toString();
    },

    /**
     * Работа с процесами
     */

    /**
     * Проверяет наличие процесса во внутренней параменной по пути
     * @param path
     */
    existsProcess: function(path){
        return typeof this.containerByPath[path] != 'undefined';
    },

    /**
     * Возвращает ссылку на интерфейсный объект модуля через который осуществляется
     * общение с JS-обвязкой модуля, либо false если по требуемому пути ничего не найдено.
     *
     * @param path - путь по меткам вызова
     */
    getProcess: function(path) {

        return this.existsProcess(path) ? this.containerByPath[path] : false;

    },

    /**
     * Добавление процесса в реестр
     * @param path - путь по меткам вызова
     * @param component - сам объект
     */
    addProcess: function( path, component ) {

        // добавление во внутренний массив ссылки
        this.containerByPath[path] = component;

        // добавление пути в объект
        component.path = path;

        // при удалении сделать очистку
        var self = this;
        component.on('destroy',function(){
            self.delProcess( path, true );
        });

        return component;

    },

    /**
     * Вычисляет путь родителя
     * @param path
     */
    getParentPath: function(path) {
        var parentPath = '';
        var dotPos = path.lastIndexOf('.');
        if ( dotPos != -1 )
            parentPath = path.substr(0, dotPos);
        return parentPath;
    },

    /**
     * Возвразает ссылку на родительский объект модуля
     * @param path
     */
    getParentProcess: function(path) {

        // путь родителя
        var parentPath = this.getParentPath(path);

        // вернуть ссылку на объект или ссылку на корень
        return this.getProcess( parentPath );

    },

    /**
     * Удаляет процесс по пути
     * @param path
     * @param [doNotDestroy]
     */
    delProcess: function(path,doNotDestroy) {

        // получить процесс
        var process = this.getProcess(path);

        // если найден
        if ( process ) {

            // рекурсивно удалить наследников
            var pattern = '/^'+path.replace(/[.]/g,'\\\.')+'[\w-]+$/i';
            for ( var path_key in this.containerByPath )
                if ( path_key.match(pattern) )
                    this.delProcess(path_key);

            // уничтожить текущий
            if ( !doNotDestroy )
                process.destroy();

            // удаление из списка
            if ( this.existsProcess(path) )
                delete this.containerByPath[path];

            // удалить прослушку
            for ( var key in this.eventQuery ) {
                if ( this.eventQuery[key][path] )
                    delete this.eventQuery[key][path];
            }

        }

        return true;

    },

    /**
     * Назначает в метку вывода модуль.
     *
     * @param path - путь по меткам вызова
     * @param moduleValues - пришедшие для модуля данные
     */
    setProcess: function (path, moduleValues) {

        // запросить объект по заданному пути
        var process = this.getProcess(path);

        // сторонняя библиотека
        var externalLib = moduleValues['externalLib'];

        if ( !moduleValues['moduleName'] ){
            sk.error( 'No modile name for ['+path+']' );
            return null;
        }

        // имя класса для создания нового объекта
        var moduleName = externalLib ? externalLib : moduleValues['moduleName'],
            layerName = moduleValues['layerName'] || moduleValues['moduleLayer'] || buildConfig.layerName,
            extModuleName = 'Ext.'+layerName+'.'+moduleName,
            dirParamName = externalLib ? 'externalLibDir' : 'moduleDir',
            moduleDir = moduleValues[dirParamName] ? moduleValues[dirParamName] : moduleValues['moduleDir']
        ;

        // установка директорий запроса
        Ext.Loader.setPath(extModuleName, moduleDir+'/js/'+moduleName+'.js');

        // дополнительные компоненты: заданы - прописать пути загрузки (не для сторонних библиотек)
        if ( typeof moduleValues['subLibs'] == 'object' ){
            for ( var subLibNameCall in moduleValues['subLibs'] ) {
                var lib = moduleValues['subLibs'][subLibNameCall];
                var libName = 'Ext.'+lib['layer']+'.'+lib['name'];
                Ext.Loader.setPath(libName, lib['dir']+'/js/'+lib['name']+'.js');
                if ( lib.call )
                    Ext.Loader.require(libName);
            }
        }

        // нет процесса по пути (или уничтожен) - создать заново
        if ( !process ) {

            var coverInit;


            if ( externalLib ) {
                coverInit = moduleValues['init'] || {};
                Ext.merge(
                    coverInit, {
                        dirName: moduleDir,
                        ifaceData: moduleValues['params']
                    }
                );
            } else {
                // перекрывающий инициализационыый массив
                coverInit = moduleValues['init'] || {};
                Ext.merge(
                    coverInit, { dirName: moduleDir }
                );
            }

            coverInit.id = this.getUniqueId();
            coverInit.skMainCont = coverInit.id;
            coverInit.moduleName = moduleName;
            coverInit.layerName = moduleValues['moduleLayer'];
            coverInit.className = moduleValues['className'];
            coverInit.path = path;

            // добавить наследников, если есть
            var childList = moduleValues['childList'] || [];
            var childInit = [];
            for ( var childPath in childList )
                childInit.push( this.getProcess( childList[childPath] ) );
            if ( childInit.length ) coverInit.items = childInit;
            coverInit.initChildList = childList;

            // добавление во внутренний массив ссылки
            process = this.addProcess( path, Ext.create( extModuleName, coverInit ) );

            // проверить наличие родителя
            var parentPath, parent;
            if ( (parentPath = this.getParentPath( path )) && (parent = this.getProcess(parentPath)) ) {
                // уже есть - добавить к нему
                parent.add(process);
            }

            // перестроение вывода
            process.doComponentLayout();

            // если есть послеинициализационный метод
            if ( process.afterRenderInterface )
                process.afterRenderInterface();

        }

        // выполнить операцию с данными
        this.executeProcess( path, moduleValues );

        return process;

    },

    /**
     * Вызывает метод execute() интерфейсного объекта модуля. Метод execute
     * ответственен за прием данных от серверной части и отображение интерфейса модуля
     * в соответствии с ними.
     *
     * @param path - путь по меткам вызова
     * @param values - данные (включая метаданные) отданные серверной частью модуля
     */
    executeProcess: function(path, values) {

        // запросить процесс
        var process = this.getProcess(path);

        // нет - выйти
        if ( !process ) return false;

        // выполнить, если есть функция выполнения
        if ( typeof process.execute == 'function' )
            return process.execute( values.params || {}, values.cmd || '' );

        return false;

    },

    /**
     * Работа с отправкой и приемом данных
     */

    /**
     * Собирает массив для отправки серверу данных от имени модулей. Отправка
     * происходит методом postData() в том случае если все подписчики на события
     * отработали fire событий (очередь не отработавших обработчиков событий пуста).
     *
     * @param path - путь по меткам вызова
     * @param data - данные (включая метаданные) отсылаемые серверной части модуля
     * @param [contName] - имя контейнера для хранения
     */
    setData: function(path, data, contName) {

        // проверку на существование не делаю, т.к. возможно потребуется
        // выполнять запись в несуществующие контейнеры

        // по умолчанию пустой объект
        if ( typeof data === 'undefined' ) data = {};

        // контейнер по умолчанию
        if ( !contName ) contName = 'bufferPack';

        // положить посылку во временный контейнер
        this[contName][path] = data;

    },

    /**
     * Запросить буфер данных на отправку
     * @param contName - имя контейнера для хранения
     */
    getBuffer: function( contName ){
        // контейнер по умолчанию
        if ( !contName ) contName = 'bufferPack';
        return this[contName];
    },

    /**
     * Очистка буфера отправки
     * @param contName - имя контейнера для хранения
     */
    clearBuffer: function( contName ){
        // контейнер по умолчанию
        if ( !contName ) contName = 'bufferPack';
        this[contName] = {};
    },

    /**
     * Отправляет json массив на серверную часть в том случае, если все подписчики на
     * события отработали fire событий (очередь не отработавших обработчиков событий пуста).
     */
    postData: function() {

        // не отправлять, если еще есть обработчики в очереди
        if ( !this.allEventsComplete() )
            return;

        var data = {};
        for ( var path in this.bufferPack ) {
            data[path] = this.bufferPack[path];
            //data.push({
            //    params: this.bufferPack[path],
            //    path: path
            //});
        }

        // если отправка перехвачена
        if ( this.isIntercepted() ) {
            // в хранилище её
            if ( this.intercept.deleteFlag )
                this.clearIntercept();
            else
                this.putInterceptToContainer( data );
        } else {

            // послать данные
            this.sendDataToServer( data, this.onCurrentSendEvents );

            // удалить события при отправке
            this.clearOnDataSend();

        }

        // очистить буфер посылок
        this.clearBuffer('');

    },

    /**
     * Набор событий на отправку текщей посылки
     */
    onCurrentSendEvents: [],

    /**
     * Добавляет обработчик на отправку текущей посылки
     * @param fnc
     */
    onDataSend: function(fnc) {
        this.onCurrentSendEvents.push( fnc );
    },

    /**
     * Очищает список событий на пекущую отправку
     */
    clearOnDataSend: function() {
        this.onCurrentSendEvents = [];
    },

    /**
     * Запускает набор обработчиков, подписанных на текущую отправку
     */
    fireOnDataSend: function( events ) {
        for ( var i in events ) {
            events[i]();
        }
    },

    /**
     * Отправить посылку с данными на сервер
     */
    sendDataToServer:function( data, events ) {

        var self = this;

        // собрать массив для отправки
        var postData = {
            sessionId: this.getSessionId(),
            layoutMode: this.getLayoutMode(),
            data: data
        };

        // сформировать параметры запроса
        var connection = Ext.create('Ext.data.Connection',{
            url: this.url,
            timeout: self.doNotUseTimeoutFlag ? 3600000 : buildConfig.request_timeout // при переключении 60 минту, по умолчанию 5 минут
        });

        // сбросить флаг блокировки запроса по длительности
        self.doNotUseTimeoutFlag = false;

        // выполнить отправку
        connection.request({
            jsonData: postData,
            scope: self,
            success: this.onSuccess,
            failure: this.onFailure
        });

        // события при отправке
        this.fireOnDataSend( events );

    },

    /**
     * Не использовать таймаут при следующем запросе
     */
    doNotUseTimeout: function() {
        this.doNotUseTimeoutFlag = true;
    },

    /**
     * При НЕудачной отправке запроса
     * @param response
     */
    onFailure: function(response){
        var data;
        var text = '';
        try {
            if (response.responseText) {
                data = eval('(' + response.responseText + ')');
                if (data && data.message)
                    text = data.message
            }
        } catch(e) {
            sk.error(sk.dict('ajax_error')+response.status+')', response.responseText);
        }

        // снять индикатор загрузки
        for (  process in this.containerByPath ) {

            if ( this.containerByPath[process] ) {
                if ( typeof(this.containerByPath[process]['unsetLoadingIndicator'])==='function' ) {
                    //noinspection JSUnresolvedFunction
                    this.containerByPath[process].unsetLoadingIndicator();
                } else {
                    this.containerByPath[process].setLoading(false);
                }
            }
        }

        sk.error(sk.dict('ajax_error')+response.status+')', text);
    },

    /**
     * При удачной отправке запроса
     * @param response
     * @param opts
     */
    onSuccess: function( response, opts ){

        var self = opts.scope,
            element_key, element,
            path,process,
            respJSONText
        ;

        try {

            var indexOfJSONStart = response.responseText.indexOf('{"sessionId":"');
            if ( indexOfJSONStart>0 ) {
                var logData = response.responseText.substr(0,indexOfJSONStart);
                processManager.fireEvent('request_add_info',logData);
                if ( !this.existsProcess('out') )
                    sk.message('Debug info in responce', logData, 'msg-error', 10000);
                respJSONText = response.responseText.substr(indexOfJSONStart);
            } else {
                respJSONText = response.responseText;
            }

            // разобранный объект
            var objResponse = Ext.JSON.decode(respJSONText);

            if ( !objResponse.success ) {
                sk.error( objResponse.message || 'false response success state' );
                return;
            }

            // дополнительные файлы
            var addFiles = objResponse['addFiles'],
                key;
            if ( addFiles ) {
                if ( addFiles['css'] && addFiles['css'].length ) {
                    for ( key in addFiles['css'] ) {
                        sk.loadCss( addFiles['css'][key] );
                    }
                }
            }

            // дополнительные файлы
            if ( addFiles ) {
                if ( addFiles['js'] && addFiles['js'].length ) {
                    for ( key in addFiles['js'] ) {
                        sk.loadJS( addFiles['js'][key] );
                    }
                }
            }

            var data = objResponse.data;

            // выполнить обработку ответов

            // контейнер: набор наследников по родителям
            var childList = {};

            var processStack = [];

            for ( element_key in data  ) {

                // элемент ответа
                element = data[element_key];

                // путь по меткам
                path = element.path;
                if ( !path ) continue;

                // запросить объект по заданному пути
                process = this.getProcess(path);

                if ( !process )
                    continue;

                // сторонняя библиотека
                var externalLib = element['externalLib'];

                if ( !element['moduleName'] )
                    continue;

                // имя класса для создания нового объекта
                var moduleName = externalLib ? externalLib : element['moduleName'];

                // сравнить класс объекта: не соврадает - грохнуть
                if ( process.moduleName != moduleName ) {
                    this.delProcess( path );
                }

            }

            // перебрать все объекты ответа
            for ( element_key in data  ) {

                // элемент ответа
                element = data[element_key];

                // путь по меткам
                path = element.path;
                if ( !path ) continue;

                // не выполнять в случае ошибки
                if ( element['moduleError'] ) {
                    // выдать ошибку
                    sk.error(
                        '<span style="font-weight: bold;">Path:</span> '+element['path']+'<br />'+
                        '<span style="font-weight: bold;">Module:</span> '+element['className']+'<br />'+
                        '<span style="font-weight: bold;">Error:</span> '+element['moduleError']+'<br />'
                    );
                    // снять индикатор загрузки
                    process = this.getProcess( path );
                    if ( process ) {
                        if ( typeof(process['unsetLoadingIndicator'])==='function' ) {
                            //noinspection JSUnresolvedFunction
                            process.unsetLoadingIndicator();
                        } else {
                            process.setLoading(false);
                        }
                    }

                    // закончить обработку записи
                    continue;
                }

                // сообщения
                var params = element.params;
                if ( params ) {
                    var item, itemId;
                    if ( params['moduleMessageList'] ) {
                        for ( itemId in params['moduleMessageList'] ) {
                            item = params['moduleMessageList'][itemId];
                            sk.message( item[0], item[1], '', item[2] );
                        }
                    }

                    // сообщения об ошибках
                    if ( params['moduleErrorList'] ) {
                        for ( itemId in params['moduleErrorList'] ) {
                            item = params['moduleErrorList'][itemId];
                            sk.error( item[0], item[1], item[2] );
                        }
                    }

                    //предупреждения
                    if ( params['moduleWarningList'] ) {
                        for ( itemId in params['moduleWarningList'] ) {
                            item = params['moduleWarningList'][itemId];
                            sk.warningWindow(item[0], item[1]);
                        }
                    }
                }

                // набор подчиненных модулей
                element.childList = childList[path] ? childList[path] : [];

                // если такого пути нет
                if ( !self.existsProcess(path) ) {

                    // путь родительского модуля
                    var parentPath = self.getParentPath( path );

                    // создать элемент
                    if ( childList[parentPath] )
                        childList[parentPath].push( path );
                    else
                        childList[parentPath] = [ path ];

                }

                // добавить модуль
                process = self.setProcess( path, element );

                // добавление процесса в стек
                processStack.push( process );

                self.hidePreloader();

                // Установка прослушки событий, пришедших из php
                if ( element['listenEvents'] ) {
                    var eventName;
                    for ( eventName in element['listenEvents'] ) {
                        this.setCallbackForListener( path, eventName, element['listenEvents'][eventName] );
                    }
                }

                // вызов событий, пришедших из php
                if ( element['fireEvents'] ) {
                    for ( itemId in element['fireEvents'] ) {
                        item = element['fireEvents'][itemId];
                        processManager.fireEvent.apply( processManager, item );
                    }
                }

            }

            // задержка (фикс от состояния гонки при инициализации)
            setTimeout(function(){
                // вызов событий в обратном порядке
                while ( processStack.length ) {
                    process = processStack.pop();
                    if ( process['afterProcessSet'] )
                        process['afterProcessSet']();
                }
            },1);

        } catch ( e ) {
            sk.error( 'request '+e );
        }

    },

    setCallbackForListener: function( path, eventName, actionName ) {

        var process = this.getProcess( path );

        var funcName = 'fnc_'+this.getUniqueId();

        process[funcName] = function() {
            var params = [];
            for (var i = 0; i<arguments.length ; i++ ) {
                params.push(arguments[i]);
            }
            processManager.setData( path, {
                cmd: actionName,
                params: params
            } );
            process.setLoading(true);
        };

        //console.log( eventName, item, element );
        processManager.addEventListener(eventName,path,funcName);
        //processManager.fireEvent.apply( processManager, item );

    },

    /**
     * Отправляет посылку серверу, если она не пуста
     */
    postDataIfExists: function(){
        if ( this.hasDataToSend() )
            this.postData();
    },

    /**
     * Запрашивает глобальную сессионную переменную
     */
    getSessionId: function(){
        /** @namespace sessionId  инициализруется на странице*/
        return window.sessionId || '';
    },

    /**
     * Запрашивает глобальную переменную с именем слоя
     */
    getLayoutMode: function (){
        /** @namespace layoutMode  инициализруется на странице*/
        return window.layoutMode || '';
    },

    /**
     * Работа с событиями
     */

    /**
     * Добавляет обработчик для определенного события
     *
     * @param eventName - имя события
     * @param listenerPath - путь по меткам
     * @param handleFunction - функция обработчик
     * @param [scale]
     */
    addEventListener: function(eventName, listenerPath, handleFunction, scale) {

        if ( !this.eventQuery[eventName] )
            this.eventQuery[eventName] = {};

        this.eventQuery[eventName][listenerPath] = {
            fnc: handleFunction,
            scale: scale
        };

    },

    /**
     * Удаляет обработчик события
     *
     * @param eventName - имя события
     * @param listenerPath - путь по меткам
     */
    removeEventListener: function(eventName, listenerPath) {

        if ( this.eventQuery[eventName] && this.eventQuery[eventName][listenerPath] )
            delete this.eventQuery[eventName][listenerPath];

    },

    /**
     * Вызывает все обработчики события
     *
     * @param eventName - имя события
     * @param data - данные для передачи в обработчик
     * ...
     */
    fireEvent: function(eventName, data) {

        var me = this,
            fnc,
            scale,
            argList = [],
            process
        ;

        // блокировка и проверка
        if ( me.setBlocker() > me.eventMaxCnt ) {
            me.resetBlocker();
            throw 'Process manager max recursion depth reached';
        }

        // если зареггистрированно такое событие
        if ( typeof me.eventQuery[eventName] === 'object' ) {

            // собираем набор аргументов
            for(var i=1; i<arguments.length; i++) {
                argList.push( arguments[i] );
            }

            // обойти подписанные на событие обработчики
            for ( var eventPath in me.eventQuery[eventName] ) {

                fnc = me.eventQuery[eventName][eventPath]['fnc'];
                scale = me.eventQuery[eventName][eventPath]['scale'];
                if ( scale ) {
                    if ( !scale.path )
                        scale.path = eventPath;
                    process = scale;
                } else {
                    process = me.getProcess(eventPath);
                    if ( !process ) {
                        /**
                         * В случае бесконечного цикла на этой ошибке при инициализации обработчика
                         * нужно указать 4 параметр (scale)
                         */
                        sk.error('Процесс с именем ['+eventPath+'] еще не загружен');
                        return;
                    }
                }

                switch ( typeof fnc ) {
                    case 'string':
                        process[fnc].apply( process, argList );
                        break;
                    case 'function':
                        me.eventQuery[eventName][eventPath]['fnc'].apply( process, argList );
                        break;
                }

            }

        }

        // декрементация счетчика
        me.unsetBlocker();

        // отослать данные при их наличии и отсутствии блокировок
        me.postDataIfExists();

    },

    /**
     * Возвращает значение через обработчик событий
     * Берет первое возвращенное значение
     *
     * @param eventName - имя события
     * @param [data] - данные для передачи в обработчик
     */
    getEventValue: function( eventName, data ) {

        var me = this,
            fnc,
            scale,
            argList = [],
            subVal,
            process
        ;

        // собираем набор аргументов
        for(var i=1; i<arguments.length; i++) {
            argList.push( arguments[i] );
        }

        // если зареггистрированно такое событие
        if ( typeof this.eventQuery[eventName] === 'object' ) {

            // обойти подписанные на событие обработчики
            for ( var eventPath in this.eventQuery[eventName] ) {

                fnc = me.eventQuery[eventName][eventPath]['fnc'];
                scale = me.eventQuery[eventName][eventPath]['scale'];
                if ( scale ) {
                    if ( !scale.path )
                        scale.path = eventPath;
                    process = scale;
                } else {
                    process = me.getProcess(eventPath);
                }

                switch ( typeof fnc ) {
                    case 'string':
                        subVal = process[fnc].apply( process, argList );
                        break;
                    case 'function':
                        subVal = me.eventQuery[eventName][eventPath]['fnc'].apply( process, argList );
                        break;
                    default:
                        continue;
                }

                // если есть результат - отдать его
                if ( typeof subVal !== 'undefined' ) return subVal;

            }

        }

        return null;

    },

    /**
     * Проверка объекта на пустоту
     * @param object
     * @return bool
     */
    isEmpty: function(object) {
        //noinspection LoopStatementThatDoesntLoopJS,JSUnusedLocalSymbols
        for( var k in object )
            return false;
        return true;
    },

    /**
     * Проверка наличия пакетов к отправке
     * @return bool
     */
    hasDataToSend: function( contName ){
        if ( !contName ) contName = 'bufferPack';
        return !this.isEmpty(this[contName]);
    },

    /**
     * Блокировки
     */

    /**
     * Добавить инкрементальную блокировку. Вызывается в паре с unsetBlocker.
     * Отправка произойдет только после снятия всех блокировок
     * @return int - текущее число блокировок
     */
    setBlocker: function(){
        return ++this.eventCnt;
    },

    /**
     * Снять инкрементальную блокировку. Вызывается в паре с setBlocker.
     * Отправка произойдет только после снятия всех блокировок
     * @return int - текущее число блокировок
     */
    unsetBlocker: function(){
        this.eventCnt--;
        if ( this.eventCnt<0 )
            this.resetBlocker();
        return this.eventCnt;
    },

    /**
     * Сброс списка блокировок
     */
    resetBlocker: function(){
        this.eventCnt = 0
    },

    /**
     * Возвращает true - если в очереди обработчиков событий пусто, и false - если
     * есть обработчики которые еще не вызывались.
     */
    allEventsComplete: function() {
        return this.eventCnt == 0;
    },

    /**
     * Возвращает корневую панель для заданного элемента
     * Поднимается вверх по родительским панелям пока не найдет
     */
    getMainContainer: function( cont ){
        while( typeof(cont.skMainCont) === 'undefined' ) {
            cont = cont.up('panel');
            if ( !cont ) {
                sk.error('Main container not found.');
                return null;
            }
        }
        return cont;
    },

    /**
     * Отправляет посылку серверу от имени заданного контейнера
     * Автоматически добавляет в нее дополнительные данные контейнера
     * @param self текущий объект
     * @param userData набор пользовательских данных
     */
    sendDataFromMainContainer: function( self, userData ) {

        var container = processManager.getMainContainer(self);
        var sendData = {};
        Ext.merge( sendData, container.serviceData || {}, userData );
        processManager.setData(container.path,sendData);
        processManager.postData();

    },

    /**
     * Создает посылку для подчиненного контейнера с заданными данными
     * @param childCont контейнер наследник для которого будет вычеслен родительский
     * @param data данные для дополнения посылки
     */
    setDataFromParent: function( childCont, data ) {

        // инициализация прерменных
        var self = this,
            cont = this.getMainContainer( childCont ),
            path = cont.path,
            pack = Ext.merge(cont.serviceData, data)
            ;

        // формирование посылки
        self.setData( path, pack );

    },

    /**
     * Вычленяет из пути модуля его псевдоним в меточной структуре
     * По сути отрезает строку от последней точки
     * @param path
     */
    getModuleAlias: function ( path ) {
        var regExp = new RegExp(/[\w-]+$/);
        var result = regExp.exec(path);
        return result[0] || '';
    },

    /**
     * убирает индикатор загрузки на пустом экране
     */
    hidePreloader: function() {
        var preloader = Ext.get('js_admin_preloader');
        if ( !preloader ) return;
        if ( processManager.existsProcess('out') )
            preloader.remove();
    },

    /*
     * Перехватчики отправки сообщений
     *
     * посылка для отправки не уходит сразу, а откладывается в спец хранилище,
     * а вызвавшему отдается ключ, по которому посылку можно либо отослать,
     * либо уничтожить
     */

    /**
     * Контейнер перехваченных посылок
     */
    interceptContainer: {},

    /**
     * Максимальное число посылок в хранилище
     */
    interceptInContMaxCnt: 3,

    /**
     * Набор данных
     */
    intercept: {

        // Число перехватов текущей посылки
        cnt: 0,

        // флаг удаления текущей посылки вместо отправки
        deleteFlag: false,

        // ключ контейнера
        contKey: ''

    },

    /**
     * Флаг наличия перехвата
     */
    isIntercepted: function() {
        return this.intercept.cnt || this.intercept.deleteFlag;
    },

    /**
     * положить данные посылки в хранилище перехваченных
     * @param data
     */
    putInterceptToContainer: function( data ) {

        // положить в контейнер
        var intercept = Ext.merge({
            data:data,
            events: this.onCurrentSendEvents
        },this.intercept);
        this.interceptContainer[intercept.contKey] = intercept;

        // очистить текущее данные перехвата
        this.clearIntercept();

        // очистить список событий при отправке
        this.clearOnDataSend();

    },

    /**
     * Очищает данные перехватчика
     */
    clearIntercept: function( key ) {

        // есть ключ
        if ( key ) {
            delete this.interceptContainer[key];
        } else {
            // нет ключа - базовый контейнер
            this.intercept = {
                cnt: 0,
                deleteFlag: false,
                contKey: ''
            };

        }

    },

    /**
     * Увеличить счетчик перехватов
     * только для активного сбора посылки
     */
    incIntercept: function() {

        // нет перехватов - создать новое имя перехвата
        if ( !this.intercept.cnt )
            this.intercept.contKey = this.getUniqueId();

        // увеличить счетчик
        this.intercept.cnt++;

        // если посылок в хранилище > максимального количества - выдать ошибку
        if ( this.intercept.cnt > this.interceptInContMaxCnt ) {
            sk.error('Достигнуто максимальное число перехваченых пакетов');
            return false;
        }

        return true;

    },

    /**
     * Уменьшить счетчик перехватов
     * активная и перехваченная посылка
     * @param key
     * @return bool true если достигнут ноль по количеству перехватов
     */
    decIntercept: function( key ) {

        // текущий перехват
        if ( key === this.intercept.contKey ) {

            if ( this.intercept.cnt === 0 ) {
                sk.error('Лишний откат счетчика перехвата пакетов');
                return false;
            }

            return !(--this.intercept.cnt);

        } else {

            /* перехват из контейнера */

            // проверить наличие контейнера
            if ( !this.interceptContainer[key] )  {
                sk.error('Контейнер перехвата пакетов не обнаружен заданного для ключа');
                return false;
            }

            if ( this.interceptContainer[key].cnt === 0 ) {
                sk.error('Лишний откат счетчика перехвата пакетов для контейнера');
                return false;
            }

            return !(--this.interceptContainer[key].cnt);

        }

    },

    /**
     * Создает перехватчик для текущей посылки
     * отдает id перехваченной посылки
     */
    interceptPostData: function() {

        // установить флаг перехвата
        this.incIntercept();

        // отдать id контейнера
        return this.intercept.contKey;

    },

    /**
     * Отправляет перехваченную посылку
     * Декрементирует счетчик и, если он равен 0, отсылает
     * только перехваченная посылка - активная должна отправиться стандартым способом
     * @param key
     */
    postInterceptedData: function( key ) {

        // уменьшить счетчик
        if ( !this.decIntercept( key ) )
            return false;

        // отправить
        if ( this.interceptContainer[key] )
            this.sendDataToServer(
                this.interceptContainer[key]['data'],
                this.interceptContainer[key]['events']
            );

        // удалить контейнер
        this.clearIntercept( key );

        return true;

    },

    /**
     * Запрещает отправку перехваченной посылки
     * Ставит метку на удаление, декрементирует счетчик и, если он равен 0, удаляет
     * активная и перехваченная посылка
     */
    terminateInterceptedData: function( key ) {

        var active = key === this.intercept.contKey;

        if ( active ) {

            this.intercept.deleteFlag = true;

        } else {

            if ( this.interceptContainer[key] )
                this.interceptContainer[key].deleteFlag = true;

        }

        // снять индикаторы загрузки с модулей
        this.unsetLoadingForPackageObjects( key );

        // уменьшить счетчик
        if ( !this.decIntercept( key ) )
            return false;

        if ( active )
            this.clearIntercept();
        else
            this.clearIntercept( key );

        return true;

    },

    /**
     * Снимает индикатор загрузки со всех объектов, пославших данные в посылке
     * @param key
     */
    unsetLoadingForPackageObjects: function( key ) {

        var pack = this.interceptContainer[key];
        if ( !pack ) return;
        pack = pack['data'];

        var processKey,
            process
            ;

        for ( processKey in pack ) {
            process = this.getProcess(processKey);
            if ( !process ) continue;
            process.setLoading( false );
        }

    },

    /**
     * Заблокировать текущую отправку посылки
     * в рамках текущего события
     */
    blockThisDataSending: function() {
        var key = this.interceptPostData();
        this.terminateInterceptedData( key );
    },

    parseUrl: function(name) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( window.location.href );
        if (null == results) {
            return '';
        }
        return results[1];
    }

});