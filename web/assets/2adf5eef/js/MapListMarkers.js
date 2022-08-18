/**
 * Класс регистрирует события открытия/закрытия дочернего фрейма редактирования карты,
 * а также служит для передачи параметров между главным окном и дочерним фреймом
 *
 */
Ext.define('Ext.sk.MapListMarkers',{

    path: 'mapListMarkers',
    layerName: 'sk',
    extend: 'Ext.Component',
    moduleName: 'mapListMarkers',

    // хранилище тикетов для передачи параметров дочернему окну
    ticketStorage: {},

    initComponent: function() {

        processManager.addEventListener( 'openEditorMapListMarkers', this.path, 'onOpenChildFrame' );
        processManager.addEventListener( 'closeEditorMapListMarkers',  this.path, 'onCloseChildFrame' );

    },

    onOpenChildFrame: function( data ) {
        // проверка наличия необходимых переменных
        if ( !data['scope'] || !data['fnc'] ) {
            sk.error('Wrong init map select data.');
            return false;
        }

        var ticket = processManager.getUniqueNum();
        this.ticketStorage[ticket] = data;

        var searchElem = this.findElemByXType( data['scope'], 'multiselectfield' ),
            showModification = this.findElemByXType( data['scope'], 'checkboxfield' );

        var queryParams = {
            mode:   data['mode'] ? data['mode'] : 'editorMap',
            cmd:   'edit',
            ticket: ticket,
            mapMode: 'list',
            entities: (searchElem !== undefined)? searchElem.getValue() : '',
            showModification: Number( (showModification !== undefined)? showModification.getValue() : 0 ),
            mapId: data['scope']['value'] ?  data['scope']['value'] : ''
        };

        // собрать ссылку
        var href = buildConfig.files_path + '?' + Ext.urlEncode(queryParams);

        // открыть в новом окне
        sk.newWindow( href );

        return true;
    },

    onCloseChildFrame: function( data ) {

        var ticket = data['ticket'];
        var value = data['value'];

        // проверка наличия необходимых переменных
        if ( !ticket || !value ) {
            sk.error('Wrong set map data.');
            return false;
        }

        // найти вызвавший объект
        var caller = this.ticketStorage[ticket];

        // выйти, если не найден
        if ( !caller ) {
            sk.error('No data in ticket storage for file selector');
            return false;
        }

        // вызвать функцию обработки
        caller['scope'][caller['fnc']](value);

        // удалить ярлык
        delete this.ticketStorage[ticket];
    },

    /**
     * Вернет ПЕРВОЕ поле типа xType среди полей,
     * объединенных в группу полей(fieldset) с данным полем scope
     * @returns {Object|undefined}
     * @param scope {Object} -  область видимости элемента интерфейса находящегося в одной группе полей с искомым элементом
     * @param xType {String} - тип элемента
     */
    findElemByXType: function( scope, xType ){

        var parentContainer = scope.up('fieldset'),
            searchElem;

        if ( parentContainer !== undefined ){
            parentContainer.items.items.forEach( function( field ){

                if ( field.getXType() === xType ){
                    searchElem = field;
                    return ;
                }
            });
        }

        return searchElem;

    }

});