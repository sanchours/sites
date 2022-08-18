// сработает когда Google Maps Api будет загружен
function initMap() {

    var mapSettings =  {
        backgroundColor: '#f4f4f4',
        center: {lat: 50, lng: 50},
        zoom: 5,
        defaultCenterMap: null
    };

    // json c настройками
   $(".js_map_container").each( function( i, mapContainerItem ){

       var settings = $.parseJSON( $(".js_map_settings", mapContainerItem).html() );

       // Если не указан цент карты или зум, то устанавливаем автоматическое масштабирование
       var autoScale = !Boolean(settings.mapSettings.center && settings.mapSettings.zoom);

       mapSettings = $.extend( mapSettings, settings.mapSettings );

       $('.js_maps', mapContainerItem).html("");

       // идентификатор карты
       var MapDiv = $('.js_maps', mapContainerItem).get(0);

       // карта
       var map = new google.maps.Map( MapDiv, { center : { lat: Number(mapSettings.center.lat), lng: Number(mapSettings.center.lng)}, zoom: Number(mapSettings.zoom)} );

       // информационное окно
       var infoWindow = new google.maps.InfoWindow();

       // при клике по карте - закрыть инф.окно
       google.maps.event.addListener(map, "click", function() {
           infoWindow.close();
       });

       // Определяем границы видимой области карты в соответствии с положением маркеров
       var bounds = new google.maps.LatLngBounds();

       // Массив объектов маркеров
       var markers = [];

       settings.markers.forEach( function( item ){

           var latLng = new google.maps.LatLng( item.latitude, item.longitude );

           var newMarker = new google.maps.Marker({
               position:  latLng,
               map:       map,
               title:     item.title,
               content:   item.popup_message,
               icon:      mapSettings.iconMarkers,
               optimized: false
           });

           if ( $.trim(newMarker.content) ){
               // Отслеживаем клик по маркеру
               google.maps.event.addListener(newMarker, "click", function() {

                   // Меняем содержимое информационного окна
                   infoWindow.setContent(item.popup_message);

                   // Показываем информационное окно
                   infoWindow.open(map, newMarker);

               });
           }

           markers.push(newMarker);

           // Расширяем границы видимой области, добавив координаты нашего текущего маркера
           bounds.extend(latLng);
       });

       if ( settings.mapSettings.clusterize ){
           var assetUrl = $(".js_asset_url").html(),
               markerCluster = new MarkerClusterer(map, markers, {imagePath: assetUrl + '/images/m'});
       }

       // Автоматически масштабируем карту так, чтобы все маркеры были в видимой области карты
       if ( autoScale ){

           if ( markers.length === 1 ){
               map.setCenter(bounds.getCenter());
               map.setZoom(14);
           } else if ( markers.length === 0 ){
               // Москва
               map.setCenter(new google.maps.LatLng(settings.mapSettings.defaultCenterMap.lat, settings.mapSettings.defaultCenterMap.lng));
               map.setZoom(settings.mapSettings.defaultCenterMap.zoom);
           } else {
               map.fitBounds(bounds);
           }

       }



    });

}


