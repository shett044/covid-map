<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Corona map tracker</title>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #description {
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
      }

      #infowindow-content .title {
        font-weight: bold;
      }

      #infowindow-content {
        display: none;
      }

      #map #infowindow-content {
        display: inline;
      }

      .pac-card {
        margin: 10px 10px 0 0;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        background-color: #fff;
        font-family: Roboto;
      }

      #pac-container {
        padding-bottom: 12px;
        margin-right: 12px;
      }

      .pac-controls {
        display: inline-block;
        padding: 5px 11px;
      }

      .pac-controls label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }

      #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 400px;
      }

      #pac-input:focus {
        border-color: #4d90fe;
      }

      #title {
        color: #fff;
        background-color: #4d90fe;
        font-size: 25px;
        font-weight: 500;
        padding: 6px 12px;
      }
      #target {
        width: 345px;
      }
    </style>
  </head>
  <body>
    <input id="pac-input" class="controls" type="text" placeholder="Search Box">
    <div id="map"></div>
    <script>
    var red_icon = 'red-dot.png';
   var purple_icon = 'purple-dot.png';
   var orange_icon = 'orange.png'

   var map;
   var markers = {};
   var success_markers = {};
   var openInfoBox = undefined;
   var openMarkerId = undefined;
   var getMarkerUniqueId = function(lat, lng) {
       return lat + '_' + lng;
   };

   var getLatLng = function(lat, lng) {
       return new google.maps.LatLng(lat, lng);
   };
    /**
    * report report marker from map.
    * @param lat  A latitude of marker.
    * @param lng A longitude of marker.
    */
   function reportData(lat, lng) {
       
       var description = markers[getMarkerUniqueId(lat,lng)].title;
       var url = 'locations_model.php?add_location&description=' + description + '&lat=' + lat + '&lng=' + lng;
       downloadUrl(url, function(data, responseCode) {
           if (responseCode === 200 && data.length > 1) {
               var markerId = getMarkerUniqueId(lat, lng); // get marker id by using clicked point's coordinate
               var manual_marker = markers[markerId]; // find marker
               manual_marker.setIcon(purple_icon);
               openInfoBox.close();
               openInfoBox.setContent("<div style=' color: purple; font-size: 25px;'> Waiting for admin confirm!!</div>");
               openInfoBox.open(map, manual_marker);

           } else {
               console.log(responseCode);
               console.log(data);
               openInfoBox.setContent("<div style='color: red; font-size: 25px;'>Inserting Errors</div>");
           }
       });
       openMarkerId = undefined
   }



  /*
   Perform Async Get request
   */
   function downloadUrl(url, callback) {
       var request = window.ActiveXObject ?
           new ActiveXObject('Microsoft.XMLHTTP') :
           new XMLHttpRequest;

       request.onreadystatechange = function() {
           if (request.readyState == 4) {
               callback(request.responseText, request.status);
           }
       };

       request.open('GET', url, true);
       request.send(null);
   }



   /**
   Callback from Get request that fetches confirmed reports.
   Each case will be shown in the map
   */
   function addConfirmedReport(locations, responseCode){
      locations = JSON.parse(locations)
      if (responseCode === 200 && locations.length > 1) {
        console.log(locations)
        var i;
        var confirmed = 0;
        for (i = 0; i < locations.length; i++) {
            [id, lat,lng, desc,dt] = locations[i]
            marker_id = createMarker(lat, lng, desc, new google.maps.LatLng(lat,lng),true)
            marker = success_markers[marker_id]
            marker.setIcon(red_icon)
            // setting custom html
            trs = [`<td id ='manual_description'><b>${desc}</b></td>`, `<tr><td>Confirmed case ${dt}</td></tr>`]
            table=  ` <table class=map1>${trs.join('\n')}</table>`
            info_html = `<div id='info_ ${markerId}'>${table}</div>`

            marker.html = `${info_html}`
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    if (openInfoBox!= undefined){ 
                        openInfoBox.close()
                    }
                    openInfoBox = new google.maps.InfoWindow();
                    openInfoBox.setContent(marker.html);
                    openInfoBox.open(map, marker);
                }
            })(marker, i));
        }
       } else {
         console.log(responseCode);
         console.log(data);
         infowindow.setContent("<div style='color: red; font-size: 25px;'>Inserting Errors</div>");
       }

   }

/*
   For GPS issues
   */
   function handleLocationError(browserHasGeolocation) {
       alert("Failed to load GPS Location: Get HTTPS certificate")
   }
 /**
    * Removes given marker from map.
    * @param {!google.maps.Marker} marker A google.maps.Marker instance that will be removed.
    * @param {!string} markerId Id of marker.
    */
   var removeMarker = function(marker, markerId) {
       if (marker.getIcon() != undefined) {
           var r = confirm("Do you want to delete awaiting marker ?");
           if (r == false) {
               return
           } else {
               //  Write code to delete row from SQL
           }
       }
       marker.setMap(null); // set markers setMap to null to remove it from map
       delete markers[markerId]; // delete marker instance from markers object
       openMarkerId = undefined
   };
      
  /**
    * Binds  click event to given marker and invokes a callback function that will remove the marker from map.
    * @param {!google.maps.Marker} marker A google.maps.Marker instance that the handler will binded.
    */
   var bindMarkerinfo = function(marker) {
       google.maps.event.addListener(marker, "rightclick", function(point) {
           var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
           var marker = markers[markerId]; // find marker
           infowindow = new google.maps.InfoWindow();
           infowindow.setContent(marker.html);
           infowindow.open(map, marker);
       });
   };

   /**
    * Binds right click event to given marker and invokes a callback function that will remove the marker from map.
    * @param {!google.maps.Marker} marker A google.maps.Marker instance that the handler will binded.
    */
   var bindRemoveMarker = function(marker) {
       google.maps.event.addListener(marker, "click", function(point) {
           var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
           var marker = markers[markerId]; // find marker
           removeMarker(marker, markerId); // remove it
       });
   };

  
/*
Creates basic marker based on lat, long, place title and place position (Gmap format)
Returns markerId
*/
    var createMarker = function(lat, lng, place_title, place_position, confirmed=false){
      markerId = getMarkerUniqueId(lat, lng)
       marker = new google.maps.Marker({
           map: map,
           title: place_title,
           icon:orange_icon,
           position: place_position,
           animation: google.maps.Animation.DROP,
           id: 'marker_' + markerId,
           html: "    <div id='info_" + markerId + "'>\n" +
               "        <table class=\"map1\">\n" +
               "            <tr>\n" +
               "                <td id ='manual_description'><b >" + place_title + "</b></td>\n" +
               "                 </tr><tr> <td>Click to remove marker</td>\n" +
                "<tr><td><input type='button' value='Report case' onclick='reportData(" + lat + "," + lng + ")'/></td></tr>\n"+
               "        </table>\n" +
               "    </div>"
       });
       if (confirmed){
        success_markers[markerId] = marker;
       }
       else
        markers[markerId] = marker;

       return markerId
    }


/*
Place marker with info window
*/
   var createPlaceMarker = function(place) {
       if (Boolean(openMarkerId)) {
           markers[openMarkerId].setAnimation(google.maps.Animation.BOUNCE)
           openInfoBox.open(map, markers[openMarkerId])
           return
       }
       if (place==undefined){console.log('createPlaceMarker(): input param is null '); return}
       if (place.geometry == undefined) {
           // If there is no place information, present latitude and longitude in the title
           lat = place.latLng.lat()
           lng = place.latLng.lng()
           place_title = place.latLng.toString()
           place_position = place.latLng
       } else {
           lat = place.geometry.location.lat()
           lng = place.geometry.location.lng()
           place_title = place.name
           place_position = place.geometry.location
       }

       markerId = createMarker(lat, lng, place_title, place_position)
       openMarkerId = markerId
       if (openInfoBox!=undefined){openInfoBox.close()}
       openInfoBox = new google.maps.InfoWindow();
       openInfoBox.setContent(markers[markerId].html);
       openInfoBox.open(map, markers[markerId]);
       
       bindRemoveMarker(markers[markerId]); // bind right click event to marker
       bindMarkerinfo(markers[markerId]); // bind infowindow with click event to marker
   }


      function initAutocomplete() {
        map = new google.maps.Map(document.getElementById('map'), {
          // center: {lat: -33.8688, lng: 151.2195},
          zoom: 13,
           mapTypeId: 'roadmap',
           mapTypeControl: false
        });
        // Try HTML5 geolocation.
       if (navigator.geolocation) {
           navigator.geolocation.getCurrentPosition(function(position) {
               var pos = {
                   lat: position.coords.latitude,
                   lng: position.coords.longitude
               };
               map.setCenter(pos);
           }, function() {
               handleLocationError(true, map.getCenter());
           });
       } else {
           // Browser doesn't support Geolocation
           handleLocationError(false, map.getCenter());
       }
        



        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());
        });

        // Add confirmed cases
       confirmedURL = 'locations_model.php?get_confirmed_locations';
       downloadUrl(confirmedURL, addConfirmedReport)

       // Listen for the event fired when the user selects a prediction and retrieve
       // more details for that place.
       searchBox.addListener('places_changed', function() {


           var places = searchBox.getPlaces();

          if (places.length == 0) {
            return;
          }
          // Clear out the old markers.
           Object.values(markers).forEach(function(marker) {
               marker.setMap(null);
           });

           // For each place, get the icon, name and location.
           var bounds = new google.maps.LatLngBounds();
           places.forEach(function(place) {
               if (!place.geometry) {
                   console.log("Returned place contains no geometry");
                   return;
               }


               var request = {
                   query: place.formatted_address,
                   fields: ['name', 'geometry'],
               };

            service = new google.maps.places.PlacesService(map);



            if (place.geometry.viewport) {
              // Only geocodes have viewport.
              bounds.union(place.geometry.viewport);
            } else {
              bounds.extend(place.geometry.location);
            }
            openMarkerId = undefined
            openInfoBox = undefined
            createPlaceMarker(place)
          });
          map.fitBounds(bounds);
        });

          /**
        * Binds click event to given map and invokes a callback that appends a new marker to clicked location.
        */
       var addMarker = new google.maps.event.addListener(map, 'click', function(e) {
           console.log(openMarkerId)
           var request = {
               location: e.latLng,
               type: ['(regions)'],
               rankBy: [google.maps.places.RankBy.DISTANCE]
           };
           service = new google.maps.places.PlacesService(map);
           service.nearbySearch(request, function(results, status) {
               createPlaceMarker(results[0])
           });


       });
      }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCPgOhAY-RUz7780qHD3e8p2kpyZVrll68&libraries=places&callback=initAutocomplete"
         async defer></script>
  </body>
</html>