/**
 * Класс для работы с историей
 */
Ext.define('Ext.sk.History',{

    extend: 'Ext.Component',
    path: 'history',

    tokenElementDelimiter: ';',
    tokenValueDelimiter: '=',

    /**
     * Предыдущий обработанный токен
     */
    previousToken: '',

    /**
     * Целевая вкладка посещена
     * Флаг для работы прямых ссылок в админке
     * Нужен для того, чтобы понять было ли уже достигнуто целевое состояние,
     *  но при этом не выключались инициализационные параметры до тех пор,
     *  пока мы не уёдем с нужной вкладки
     */
    initTabVisited: false,

    init_tab: '',
    init_param: '',

    initComponent: function(){

        var self=this;

        this.callParent();

        processManager.addProcess( this.path, this );

        // инициализация работы с историей браузера
        Ext.History.init();

        // заполнение стартовых значений, если были переданы
        var fragment = self.getNowTokenData();
        if ( fragment['init_tab'] )
            self.init_tab = fragment['init_tab'];
        if ( fragment['init_param'] )
            self.init_param = fragment['init_param'];


        // добавить обработчик на изменение истории
        Ext.History.on('change', function(token) {

            if (token) {

                var data = self.getTokenData( token );

                // блок отправки
                processManager.setBlocker();

                // выполнить событие для сбора данных
                processManager.fireEvent( 'location_render', data );

                // снять блок
                processManager.unsetBlocker();

                // отослать данные, если есть
                processManager.postDataIfExists();

                // проверка инициализационных параметров модуля
                if ( data['init_param'] )
                    self.init_param = data['init_param'];
                if ( data['init_tab'] ) {
                    self.init_tab = data['init_tab'];
                    // если мы уже находились на нужной вкладке, то переинициализируем её
                    // например при открытии нескольких товаров из поиска для одного раздела
                    if ( data['out.tabs'] === self.init_tab ) {
                        processManager.fireEvent('tabs_reload_current');
                    }

                }

                self.previousToken = token;

            }

        });

        // добавить подписку на изменение контрольной точки истории
        processManager.addEventListener('location_change',this.path,'onLocationChange');

        processManager.addEventListener( 'get_module_init_param', this.path, this.getModuleInitParam);


    },

    /**
     * Возвращает объект разобранных данных токена
     */
    getTokenData: function( token ){

        if ( !token ) return {};

        var self = this,
            parts, length, i, data={}
            ;

        parts = token.split(self.tokenElementDelimiter);
        length = parts.length;

        for (i = 0; i < length ; i++) {
            var valParts = parts[i].split(self.tokenValueDelimiter);
            data[valParts[0]] = valParts[1];
        }

        return data;

    },

    /**
     * Возвращает объект разобранных данных текущего токена
     */
    getNowTokenData: function(){

        var me = this;

        return me.getTokenData( Ext.History.getToken() );

    },

    /**
     * при обновлении по F5 и прямом переходе
     */
    afterRender: function() {

        var oldToken = Ext.History.getToken();
        if ( oldToken )
            Ext.History.fireEvent('change',oldToken);

    },

    // обработчик изменения контрольной точки истории
    onLocationChange: function() {

        // инициализация параменных
        var elements = [],
            me = this,
            oldToken, newToken, pack;

        // выполнить событие для сбора данных
        processManager.fireEvent( 'location_set_value' );

        // взять их и запаковать в токен
        pack = processManager.getBuffer('locPack');

        // проверить, есть ли данные для отправки
        if ( pack ) {

            for ( var path in pack ) {
                if ( typeof pack[path] !== 'object' )
                    elements.push(path+this.tokenValueDelimiter+pack[path])
            }

            // при уходе с целевой вкладки снимаем инициализационные параметры модуля
            if ( me.initTabVisited && me.init_tab !== pack['out.tabs'] ) {
                me.init_tab = '';
                me.init_param = '';
                me.initTabVisited = false;
            }

            // если инициализационные параметры активны - оставляем их в пути
            if ( me.init_tab )
                elements.push('init_tab'+this.tokenValueDelimiter+me.init_tab);
            if ( me.init_param )
                elements.push('init_param'+this.tokenValueDelimiter+me.init_param);

            // очистить очередь отправки
            processManager.clearBuffer('locPack');

        } else return false;

        // новый токен
        newToken = elements.join(this.tokenElementDelimiter);

        // старый
        oldToken = Ext.History.getToken();

        /**
         * если старый токен пуст или
         * новый является частью старого (но разным по набору параметров - включаем разделитель,
         *  чтобы отсечь включение по части значения)
         */
        if (oldToken === null || oldToken.search(newToken+this.tokenValueDelimiter) === -1) {
            Ext.History.add(newToken);
        }

    },

    /**
     * Вызывает перестроение url кода состояния
     */
    locationChange: function() {
        processManager.fireEvent('location_change');
    },

    /**
     * Отдаёт инициализационные данные, если вкладка подходит и очистить после этого
     */
    getModuleInitParam: function(){

        var me = this;
        var fragment = me.getNowTokenData();

        if ( fragment['out.tabs'] && fragment['out.tabs'] === me.init_tab) {
            var val = me.init_param;
            me.initTabVisited = true;
            return val;
        }

    }

});
