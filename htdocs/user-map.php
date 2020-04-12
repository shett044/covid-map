

<?php
   include_once 'header.php';
   include 'locations_model.php';
   //get_unconfirmed_locations();exit;
   ?>
<head>
   <!--<script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?language=en&key=AIzaSyCPgOhAY-RUz7780qHD3e8p2kpyZVrll68">
      </script>
      -->
   <style>
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
   </style>
</head>
<body>
   <h4> Enter location : 
      <input id="pac-input" class="controls" type="text" placeholder="Type address">
   </h4>
   <div id="map"></div>

   <script>
      // This example adds a search box to a map, using the Google Place Autocomplete
      // feature. People can enter geographical searches. The search box will return a
      // pick list containing a mix of places and predicted search terms.
      
      // This example requires the Places library. Include the libraries=places
      // parameter when you first load the API. For example:
      // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">
          
    /**
     * report report marker from map.
     * @param lat  A latitude of marker.
     * @param lng A longitude of marker.
     */
        var red_icon =  'http://maps.google.com/mapfiles/ms/icons/red-dot.png' ;
        var purple_icon =  'http://maps.google.com/mapfiles/ms/icons/purple-dot.png' ;
        
      function reportData(lat,lng) {
                var description = document.getElementById('manual_description').textContent;
                var url = 'locations_model.php?add_location&description=' + description + '&lat=' + lat + '&lng=' + lng;
                downloadUrl(url, function(data, responseCode) {
                    if (responseCode === 200  && data.length > 1) {
                        var markerId = getMarkerUniqueId(lat,lng); // get marker id by using clicked point's coordinate
                        var manual_marker = markers[markerId]; // find marker
                        manual_marker.setIcon(purple_icon);
                        infowindow.close();
                        infowindow.setContent("<div style=' color: purple; font-size: 25px;'> Waiting for admin confirm!!</div>");
                        infowindow.open(map, manual_marker);
        
                    }else{
                        console.log(responseCode);
                        console.log(data);
                        infowindow.setContent("<div style='color: red; font-size: 25px;'>Inserting Errors</div>");
                    }
                });
                openMarkerId=undefined
            }
        
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
        function handleLocationError(browserHasGeolocation) {
            alert("Failed to load GPS Location")
        }
        var map ;
        var markers = {};
        var openInfoBox;
        var openMarkerId = undefined
        var getMarkerUniqueId= function(lat, lng) {
            return lat + '_' + lng;
        };
      
        var getLatLng = function(lat, lng) {
            return new google.maps.LatLng(lat, lng);
        };
        var removeMarker = function(marker, markerId) {
            if (marker.getIcon() != undefined){
                var r = confirm("Do you want to delete awaiting marker ?");
                        if (r == false) {
                            return
                        } 
                        else{
                            //  Write code to delete row from SQL
                        }
            }
            marker.setMap(null); // set markers setMap to null to remove it from map
            delete markers[markerId]; // delete marker instance from markers object
            openMarkerId=undefined
        };
        /**
         * Binds  click event to given marker and invokes a callback function that will remove the marker from map.
         * @param {!google.maps.Marker} marker A google.maps.Marker instance that the handler will binded.
         */
        var bindMarkerinfo = function(marker) {
            google.maps.event.addListener(marker, "click", function (point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
                var marker = markers[markerId]; // find marker
                infowindow = new google.maps.InfoWindow();
                infowindow.setContent(marker.html);
                infowindow.open(map, marker);
                // removeMarker(marker, markerId); // remove it
            });
        };
      
        /**
         * Binds right click event to given marker and invokes a callback function that will remove the marker from map.
         * @param {!google.maps.Marker} marker A google.maps.Marker instance that the handler will binded.
         */
        var bindRemoveMarker = function(marker) {
            google.maps.event.addListener(marker, "rightclick", function (point) {
                var markerId = getMarkerUniqueId(point.latLng.lat(), point.latLng.lng()); // get marker id by using clicked point's coordinate
                var marker = markers[markerId]; // find marker
                removeMarker(marker, markerId); // remove it
            });
        };
         /**
         * Removes given marker from map.
         * @param {!google.maps.Marker} marker A google.maps.Marker instance that will be removed.
         * @param {!string} markerId Id of marker.
         */
        
      var createPlaceMarker = function(place){
                if (Boolean(openMarkerId)){
                    markers[openMarkerId].setAnimation(google.maps.Animation.BOUNCE)
                    openInfoBox.open(map, markers[openMarkerId])
                    return
                }
                if(place.geometry == undefined){
                    // If there is no place information, present latitude and longitude in the title
                    lat = place.latLng.lat()
                    lng = place.latLng.lng()
                    place_title = place.latLng.toString()
                    place_position = place.latLng
                }
                else{
                    lat = place.geometry.location.lat()
                    lng = place.geometry.location.lng()
                    place_title = place.name
                    place_position = place.geometry.location
                }
                    
                markerId = getMarkerUniqueId(lat,lng)
                markers[markerId]=new google.maps.Marker({
                    map: map,
                    title: place_title,
                    position: place_position,
                    animation:  google.maps.Animation.DROP,
                    id: 'marker_' + markerId,
                    html: "    <div id='info_"+markerId+"'>\n" +
                    "        <table class=\"map1\">\n" +
                    "            <tr>\n" +
                    "                <td id ='manual_description'><b >"+place_title+"</b></td>\n" +
                    "                 </tr><tr> <td>Long press(mobile) or Right click (web) to remove marker</td>\n" +
                    "                <td><div placeholder='Description'></textarea></td></tr>\n" +
                    "            <tr><td></td><td><input type='button' value='Report' onclick='reportData("+lat+","+lng+")'/></td></tr>\n" +
                    "        </table>\n" +
                    "    </div>"
                });
                infowindow = new google.maps.InfoWindow();
                openMarkerId = markerId
                infowindow.setContent(markers[markerId].html);
                infowindow.open(map, markers[markerId]);
                openInfoBox = infowindow
                bindRemoveMarker(markers[markerId]); // bind right click event to marker
                bindMarkerinfo(markers[markerId]); // bind infowindow with click event to marker
            }


        function initAutocomplete() {

   
            

    //   function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    //     infoWindow.setPosition(pos);
    //     infoWindow.setContent(browserHasGeolocation ?
    //                           'Error: The Geolocation service failed.' :
    //                           'Error: Your browser doesn\'t support geolocation.');
    //     infoWindow.open(map);
    //   }

        map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: -33.8688, lng: 151.2195},
          zoom: 13,
          mapTypeId: 'roadmap'
        });
        //  infoWindow = new google.maps.InfoWindow;

        // Try HTML5 geolocation.
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            // infoWindow.setPosition(pos);
            // infoWindow.setContent('Location found.');
            // infoWindow.open(map);
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
            //   markers = [];
        
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
                openMarkerId=undefined
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
                        rankBy:[google.maps.places.RankBy.DISTANCE]
                    };
            service = new google.maps.places.PlacesService(map);
            service.nearbySearch(request, function(results,status){createPlaceMarker(results[0]) });

            
        });
        
      
       
      
      
        /**
         * loop through (Mysql) dynamic locations to add markers to map.
        
        var i ; var confirmed = 0;
        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                map: map,
                icon :   locations[i][4] === '1' ?  red_icon  : purple_icon,
                html: "<div>\n" +
                "<table class=\"map1\">\n" +
                "<tr>\n" +
                "<td><a>Description:</a></td>\n" +
                "<td><textarea disabled id='manual_description' placeholder='Description'>"+locations[i][3]+"</textarea></td></tr>\n" +
                "</table>\n" +
                "</div>"
            });
      
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infowindow = new google.maps.InfoWindow();
                    confirmed =  locations[i][4] === '1' ?  'checked'  :  0;
                    $("#confirmed").prop(confirmed,locations[i][4]);
                    $("#id").val(locations[i][0]);
                    $("#description").val(locations[i][3]);
                    $("#form").show();
                    infowindow.setContent(marker.html);
                    infowindow.open(map, marker);
                }
            })(marker, i));
        }
      */
        
      
      }
      
   </script>
   <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCPgOhAY-RUz7780qHD3e8p2kpyZVrll68&libraries=places&callback=initAutocomplete"
      async defer></script>
   <?php
      include_once 'footer.php';
      
      ?>
</body>