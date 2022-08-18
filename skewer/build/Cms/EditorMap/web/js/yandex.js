var MapApi = {

    /**
     * Объект карты
     * @var {Object} - тип google.maps.Map map
     */
    map: {},

    /**
     * Менеджер объектов. Позволяет оптимально отображать, кластеризовать и управлять видимостью объектов
     * @var {Object}
     */
    objectManager: {},

    /**
     * Данные пришедшие от сервера
     * @var {Object} data
     */
    data: {
        settings: {},
        markers: [],
    },

    /**
     * Маркеры расположенные на карте
     * @var {Array} markersOnMap
     */
    markersOnMap: [],

    /**
     * Возможности данного компонента
     * @var {Object} capabilities
     */
    capabilities: {
        addMarkerByClick:  true,
        addMarkerFromInput: true,
        searchLine: true
    },

    /**
     * Флаг явного задания центра карты
     */
    centerDefined: false,

    /**
     * Центр карты(по умолчанию) - г.Москва
     * @var {Object} defaultCenterMap
     */
    defaultCenterMap: {},

    /**
     * Функция инициализации.
     * Читает параметры из html-шаблона и иниц-ет ими данный объект
     */
    init: function () {
        var JSONsettings = $('#js_settings').html(),
            JSONMarkers  = $("#js_marker").html(),
            JSONcapabilities = $("#js_capabilities").html();

        if (JSONsettings)
            this.data.settings = JSON.parse(JSONsettings);

        if ( JSONMarkers )
            this.data.markers = JSON.parse(JSONMarkers);

        if ( JSONcapabilities )
            this.capabilities = JSON.parse(JSONcapabilities);

        this.defaultCenterMap = {
            center: [ this.data.settings.defaultCenterMap.lat, this.data.settings.defaultCenterMap.lng ],
            zoom: this.data.settings.defaultCenterMap.zoom,
            controls: []
        };

    },

    /**
     * Создаёт карту
     * @returns {MapApi}
     */
    createMap: function ( idSelector ) {

        this.init();

        // по умолчанию центр - г.Москва
        var oCenter = this.defaultCenterMap,
            me = this;

        if (!($.isEmptyObject( this.data.settings.center ))) {
            oCenter = {
                center: [this.data.settings.center.lat, this.data.settings.center.lng],
                zoom:    this.data.settings.zoom,
                controls: []
            };
            this.centerDefined = true;

        }

        this.map = new ymaps.Map( idSelector, oCenter );
        this.updateCenterAndZoom( oCenter.center.join(','), oCenter.zoom );

        // Событие изменения области просмотра карты
        this.map.events.add('boundschange', function ( event ) {
            me.updateCenterAndZoom( event.get('newCenter').join(','), event.get('newZoom') );
        });

        return this;

    },
    /**
     * Добавляет маркер на карту
     * @param {Array} Coords
     */
    addMarker: function( Coords ){

        this.objectManager = new ymaps.ObjectManager();
        this.map.geoObjects.add( this.objectManager );

        var newMarker = {
            "type": "Feature",
            "id": this.markersOnMap.length + 1,
            "geometry": {
                "type": "Point",
                "coordinates": Coords
            }
        };

        this.markersOnMap.push( newMarker );

        // Добавляем маркеры в менеджер объектов
        this.objectManager.add({
            "type": "FeatureCollection",
            "features": this.markersOnMap
        });

        this.updateLatLngFields(Coords);
        this.reverseGeoCode(Coords);
    },

    /**
     * Удаляет все маркеры
     */
    deleteAllMarkers: function(){
        this.map.geoObjects.removeAll();
        this.markersOnMap = [];
    },

    /**
     * Обновляет поля: - широта, долгота
     * @param {Array} coords
     */
    updateLatLngFields: function( coords ){
        $('#js_map_lat').val(coords[0]);
        $('#js_map_lng').val(coords[1]);
    },

    /**
     * Обновляет поля центр/зум
     * @param {*} center
     * @param {*} zoom
     */
    updateCenterAndZoom: function( center, zoom ){
        $('#js_map_center').val(center);
        $('#js_map_zoom').val(zoom);
    },

    /**
     * Автомасштабирование
     */
    autoScale: function(){

        if ( this.data.markers.length === 0 ){
            // Москва
            this.map.setCenter( this.defaultCenterMap.center, this.defaultCenterMap.zoom );

        } else {

            // Вычисляет центр и уровень масштабирования, которые необходимо установить карте для того, чтобы полностью отобразить переданную область.
            var centerAndZoom = ymaps.util.bounds.getCenterAndZoom( this.objectManager.getBounds(), this.map.container.getSize(), this.map.options.get('projection') );

            // Если маркер один, то обрезаем zoom до максимально возможной величины
            if ( this.data.markers.length == 1 )
                centerAndZoom.zoom = 14;

            this.map.setCenter( centerAndZoom.center, centerAndZoom.zoom );

        }

    },

    /**
     * Изменить положение карты. Установить новый центр и зум
     * @param {Array}  center
     * @param {Number} zoom
     */
    changeMapPosition: function( center, zoom ){
        this.map.setCenter( center );
        this.map.setZoom( zoom );
    },
    /**
     * Обновляет (нескрытые, доступные для редактирования) поля широта/долгота
     * @param {Array} coords - координаты
     */
    updateInputLatLng: function( coords ){
        $('#js_input_lat').val(coords[0]);
        $('#js_input_lng').val(coords[1]);
    },

    /**
     * Обратное геокодирование (координаты -> ардес)
     * @param {Array} coords - координаты
     */
    reverseGeoCode: function ( coords ) {

        var reverseGeocoder = ymaps.geocode(coords);

        reverseGeocoder.then(
            function (res) {
                var nearest = res.geoObjects.get(0);

                $("#js_map_address").val( nearest.properties.get('text') );
            },
            function (err) {
                alert('Произошла ошибка при выполнении обратного геокодирования');
            }
        );
    }


};

/**
 * Добавляет в карту функциональность поисковой строки
 * @param oMapApi
 */
function searchLineInit(){

    // Создадим экземпляр элемента управления «поиск по карте»
    var searchControl = new ymaps.control.SearchControl({
        options: {
            provider: 'yandex#map'
        }
    });

    //Добавим строку поиска в элементы управления карты
    MapApi.map.controls.add(searchControl);

    searchControl.hideResult();

    // событие выбора результата поиска
    searchControl.events.add('resultselect', function ( event ) {

        var results = searchControl.getResultsArray(),
            selected = event.get('index'),
            coords;

        MapApi.deleteAllMarkers();

        coords = results[selected].geometry.getCoordinates();

        //обновляем поля
        MapApi.updateLatLngFields(coords);
        MapApi.updateInputLatLng(coords);
        MapApi.reverseGeoCode(coords);

    });

    return searchControl;

}


/**
 * Функция инициализации карты.
 * Начнет выполнятся после загрузке скрипта YandexMapsApi
 */
function initMap() {

    var map = MapApi.createMap('js_map'),
        searchControl;

    if ( MapApi.capabilities.searchLine )
        searchControl = searchLineInit();

    MapApi.data.markers.forEach( function( item ){
        MapApi.addMarker( [item.latitude, item.longitude] );
    });

    // Если режим - редактирования маркера
    if ( MapApi.data.markers.length == 1 ){
        var coords = [MapApi.data.markers[0].latitude, MapApi.data.markers[0].longitude];
        MapApi.updateInputLatLng(coords);
        MapApi.reverseGeoCode(coords);

    }

    if ( !MapApi.centerDefined ){
        MapApi.autoScale();
    }

    if ( MapApi.capabilities.addMarkerByClick ){

        MapApi.map.events.add('click', function( mapEvent ){
            // Координаты в текущей точке
            var coords = mapEvent.get('coords');

            if ( searchControl !== undefined )
                searchControl.hideResult();

            MapApi.deleteAllMarkers();
            MapApi.addMarker( coords );
            MapApi.updateInputLatLng( coords );

        });

    }

}


$(document).ready(function(){

    $("body").on( 'submit', '#js_set_marker', function( event ){

        event.preventDefault();

        var lat = $('#js_input_lat').val(),
            lng = $('#js_input_lng').val();

        try{
            validateCoordinates(lat, lng);

        } catch (error){
            sk.error(error.message);
            return false;
        }

        var newCenter = [lat, lng];

        MapApi.changeMapPosition( newCenter, MapApi.map.getZoom() );
        MapApi.updateCenterAndZoom( newCenter, MapApi.map.getZoom() );

        MapApi.deleteAllMarkers();
        MapApi.addMarker(newCenter);
        MapApi.updateLatLngFields(newCenter);
        MapApi.reverseGeoCode(newCenter);

    });


});