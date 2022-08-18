function initMap() {

    // json c настройками
    $(".js_map_container").each( function( i, mapContainerItem ){

        var settings = $.parseJSON( $(".js_map_settings", mapContainerItem).html() );

        var MapDiv = $('.js_maps', mapContainerItem).get(0);

        $('.js_maps', mapContainerItem).html("");

        // Если не указан цент карты или зум, то устанавливаем автоматическое масштабирование
        var autoScale = !Boolean(settings.mapSettings.center && settings.mapSettings.zoom);

        var mapSettings =  {
            center: {lat: 50, lng: 50},
            zoom: 5,
            defaultCenterMap: null
        };

        mapSettings = $.extend( mapSettings, settings.mapSettings );

        // карта
        var myMap = new ymaps.Map( MapDiv, {
            center: [ mapSettings.center.lat, mapSettings.center.lng ],
            zoom:   mapSettings.zoom
        });

        // закрытие всплывающего окна при клике по карте
        myMap.events.add('click', function() {
            myMap.balloon.close();
        });

        // Менеджер объектов
        var objectManager = new ymaps.ObjectManager({
            clusterize: mapSettings.clusterize
        });

        myMap.geoObjects.add(objectManager);

        var markers = [];

        settings.markers.forEach( function( marker, index ){
            var newMarker = {
                "type": "Feature",
                "id": index,
                "geometry": {
                    "type": "Point",
                    "coordinates": [ marker.latitude, marker.longitude ]
                },
                "properties": {
                    hintContent: marker.title,
                    clusterCaption: marker.title
                },
            };

            // Добавляем всплывалку
            if ( $.trim(marker.popup_message) )
                newMarker.properties.balloonContent = marker.popup_message;

            // Добавляем собственную картинку
            if ( $.trim(mapSettings.iconMarkers) ){
                newMarker.options = {
                    iconLayout: 'default#image',
                        iconImageHref: mapSettings.iconMarkers
                };
            }

            markers.push(newMarker);

        });


        // Добавляем маркеры в менеджер объектов
        objectManager.add({
            "type": "FeatureCollection",
            "features": markers
        });

        // Если включено автомасштабирование
        if ( autoScale ){

            if ( markers.length === 0 ){
                myMap.setCenter( [settings.mapSettings.defaultCenterMap.lat, settings.mapSettings.defaultCenterMap.lng], settings.mapSettings.defaultCenterMap.zoom );

            } else {

                // Вычисляет центр и уровень масштабирования, которые необходимо установить карте для того, чтобы полностью отобразить переданную область.
                var centerAndZoom = ymaps.util.bounds.getCenterAndZoom( objectManager.getBounds(), myMap.container.getSize(), myMap.options.get('projection') );

                // Если маркер один, то обрезаем zoom до максимально возможной величины
                if ( markers.length == 1 )
                    centerAndZoom.zoom = 14;

                myMap.setCenter( centerAndZoom.center, centerAndZoom.zoom );

            }

        }


    });


}