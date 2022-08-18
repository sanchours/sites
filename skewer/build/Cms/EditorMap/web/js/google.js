var MapApi = {

    /**
     * Объект карты
     * @var {Object} - тип google.maps.Map map
     */
    map: {},

    /**
     * Объект геокодер
     * @var {Object} - google.maps.Geocoder
     */
    geocoder: {},

    /**
     * Границы видимой области
     * @var {Object}
     */
    bounds: {},

    /**
     * Данные пришедшие от сервера
     * @var {Object} data
     */
    data: {
        settings: {},
        markers: []
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
            center: new google.maps.LatLng(this.data.settings.defaultCenterMap.lat, this.data.settings.defaultCenterMap.lng),
            zoom: this.data.settings.defaultCenterMap.zoom
        };

        this.bounds = new google.maps.LatLngBounds();



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
                center: new google.maps.LatLng(this.data.settings.center.lat, this.data.settings.center.lng),
                zoom: Number(this.data.settings.zoom)
            };
            this.centerDefined = true;

        }

        this.map = new google.maps.Map(document.getElementById(idSelector), oCenter);
        this.geocoder = new google.maps.Geocoder;
        this.updateCenterAndZoomFields( oCenter.center, oCenter.zoom );

        google.maps.event.addListener( this.map, 'bounds_changed', function () {
            me.updateCenterAndZoomFields( this.getCenter(), this.getZoom() );
        });

        if ( this.capabilities.addMarkerByClick ){

            google.maps.event.addListener( this.map, 'click', function ( event ) {
                me.deleteAllMarkers();
                me.addMarker( event.latLng );
                me.updateLatLngFields( event.latLng );
                me.geocodeLatLng( event.latLng );
                me.updateInputLatLng( event.latLng );
            });

        }

        return this.map;

    },
    /**
     * Добавляет маркер на карту
     * @param {google.maps.LatLng} location - объект с координатами маркера
     * @returns {google.maps.Marker}
     */
    addMarker: function( location ){
        var marker = new google.maps.Marker({
            position: location,
            map: this.map
        });
        this.markersOnMap.push(marker);
        // Расширяем границы видимой области
        this.bounds.extend(location);
        return marker;
    },

    /**
     * Удаляет все маркеры на карте
     */
    deleteAllMarkers: function () {
        this.setMap4Markers(null);
        this.markersOnMap = [];
    },

    /**
     * Утанавливает объект карты map для всех маркеров
     * @param {google.maps.Map} map - карта.
     */
    setMap4Markers: function ( map ) {
        for (var i = 0; i < this.markersOnMap.length; i++) {
            this.markersOnMap[i].setMap( map );
        }
    },

    /**
     * Обновляет поля: - широта, долгота
     * @param {google.maps.LatLng} location - объект с координатами
     */
    updateLatLngFields: function ( location ) {
        $('#js_map_lat').val(location.lat());
        $('#js_map_lng').val(location.lng());
    },

    /**
     * Обновляет поля: - центр, зум
     * @param {google.maps.LatLng} center
     * @param zoom
     */
    updateCenterAndZoomFields: function ( center, zoom ) {
        $('#js_map_center').val([center.lat(), center.lng()].join(','));
        $('#js_map_zoom').val(Number(zoom));
    },

    /**
     * Автомасштабирование
     */
    autoScale: function(){
        if ( this.markersOnMap.length === 1 ){
            this.map.setCenter(this.bounds.getCenter());
            this.map.setZoom(14);
        } else if ( this.markersOnMap.length === 0 ){
            this.map.setCenter(this.defaultCenterMap.center);
            this.map.setZoom(this.defaultCenterMap.zoom);
        } else {
            this.map.fitBounds(this.bounds);
        }
    },

    changeMapPosition: function( center, zoom ){
        this.map.setCenter( center );
        this.map.setZoom( zoom );
    },
    /**
     * Обновляет видимые(доступные для редактирования) поля  широта, долгота
     * @param {google.maps.LatLng} location - объект с координатами
     */
    updateInputLatLng: function( location ){
        $('#js_input_lat').val(location.lat());
        $('#js_input_lng').val(location.lng());

    },

    /**
     * Выполняет обратное геокодирование(координаты->адрес).
     * @param {google.maps.LatLng} location - объект с координатами
     */
    geocodeLatLng: function(location){

        this.geocoder.geocode({'location': location}, function(results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    $("#js_map_address").val(results[0].formatted_address);
                } else {
                    $("#js_map_address").val('');
                }
            } else {
                $("#js_map_address").val('');
            }
        });
    }


};

/**
 * Добавляет в карту функциональность поисковой строки
 * @param oMapApi
 */
function searchLineInit( oMapApi ){

    var input = document.getElementById('js_input_search');

    var autocomplete = new google.maps.places.Autocomplete(input);
    oMapApi.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    autocomplete.bindTo('bounds', oMapApi.map);

    autocomplete.addListener('place_changed', function(){

        var place = this.getPlace();

        if (!place.geometry) {
            window.alert("Не корректный адрес");
            return;
        }

        if (place.geometry.viewport) {
            oMapApi.map.fitBounds(place.geometry.viewport);
        } else {
            oMapApi.map.setCenter(place.geometry.location);
            oMapApi.map.setZoom(17);
        }

        oMapApi.deleteAllMarkers();
        oMapApi.addMarker(place.geometry.location);
        oMapApi.updateLatLngFields( place.geometry.location );
        oMapApi.geocodeLatLng(place.geometry.location);
        oMapApi.updateInputLatLng( place.geometry.location );

        this.setTypes([]);
    });

}


/**
 * Функция инициализации карты.
 * Начнет выполнятся после загрузке скрипта GoogleMapsApi
 */
function initMap() {

   MapApi.createMap('js_map');

    if ( MapApi.capabilities.searchLine ){
        searchLineInit( MapApi );
    }

    MapApi.data.markers.forEach( function ( item ) {
        var location = new google.maps.LatLng(item.latitude, item.longitude );
        MapApi.addMarker( location );
        MapApi.updateLatLngFields( location );
    });

    // Если режим - редактирования маркера
    if ( MapApi.data.markers.length == 1 ){
        var latlng = new google.maps.LatLng(MapApi.data.markers[0].latitude, MapApi.data.markers[0].longitude );
        MapApi.updateInputLatLng( latlng );
        MapApi.geocodeLatLng( latlng );

    }


    // Если центр/зум карты не был задагн явно - применяем автомасштабирование
    if ( !MapApi.centerDefined ){
        MapApi.autoScale();
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

        var newCenter = new google.maps.LatLng(lat, lng);

        MapApi.changeMapPosition( newCenter, MapApi.map.getZoom() );
        MapApi.updateCenterAndZoomFields( newCenter, MapApi.map.getZoom() );

        MapApi.deleteAllMarkers();
        MapApi.addMarker(newCenter);
        MapApi.updateLatLngFields(newCenter);
        MapApi.geocodeLatLng(newCenter);

    });


});