<?php

function iri_inline_scripts( $atts ) {

    //* Figure out our variables (we may need them later)
    $client_id = $atts[ 'client_id' ];
    $brand_id = $atts[ 'brand_id' ];
    $google_maps_api_key = $atts[ 'google_maps_api_key' ];
    $starting_zip = $atts[ 'starting_zip' ];
    $map_default_zoom_level = $atts[ 'map_default_zoom_level' ];
    $all_products_group_to_rename = $atts[ 'all_products_group_to_rename' ];
    $contact_url = $atts[ 'contact_url' ];
    $radius = $atts[ 'search_radius' ];

    ?>

    <!-- We've resorted to inlining these scripts because the Google script doesn't appear possible to enqueue with a shortcode implementation -->
    <script>

        // Define our ajaxurl
        var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";

        jQuery( document ).ready(function($) {

            google.maps.event.addDomListener(window, 'load', init);
            var map;
            var markers = [];
            function init() {

                //jQuery.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address=' + $('#postalCode').val() + '&key=<?php // echo $google_maps_api_key; ?> ', null, function (zipData) {
                jQuery.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address=<?php echo $starting_zip; ?>&key=<?php echo $google_maps_api_key; ?>', null, function (zipData) {
                    var p = zipData.results[0].geometry.location;
                    var centerMap = new google.maps.LatLng(p.lat, p.lng);
                    // var MY_MAPTYPE_ID = 'LALA Foods Product Locator';

                    var mapOptions = {
                        center: centerMap,
                        zoom: <?php echo $map_default_zoom_level; ?>,
                        zoomControl: true,
                        zoomControlOptions: {
                            style: google.maps.ZoomControlStyle.SMALL,
                        },
                        disableDoubleClickZoom: true,
                        mapTypeControl: false,
                        scaleControl: true,
                        scrollwheel: true,
                        panControl: true,
                        streetViewControl: true,
                        draggable : true,
                        overviewMapControl: true,
                        overviewMapControlOptions: {
                            opened: false,
                        },
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                    }
                    var mapElement = document.getElementById('map-canvas');
                    map = new google.maps.Map(mapElement, mapOptions);
                });
            }



            $.getJSON( ajaxurl,
                {
                  action: 'get_product_list',
                },
                function( data ) {
                    var items = '';
                    $.each( data, function( key, val ) {
                        /*for(var upcCode in val) {
                         console.log(upcCode);
                         }*/
                        // console.log(key + ' ' + val);
                        $.each( val, function (upcCode, upcName) {
                            //console.log("key: " + upcCode + " value: " + upcName );
                            items += '<option value="' + upcCode + '">' + upcName + '</option>';
                        });

                    });

                    $('#productID').html(items).show().change();

                    $('.loading-products').hide();
            });

            $('#productID').on('change', function () {
               if ($(this).val().indexOf('GROUP') != -1)
                   $(this).addClass('group-selected');
                else
                   $(this).removeClass('group-selected');
            });


            $('#locator-form').on("submit", function(e) {
                e.preventDefault();
                $('.search.locations').hide().next('.loading').removeAttr('hidden');
                // _gaq.push(['_trackEvent', 'Find a location', 'Click', 'Where to buy']);

                $('#locationsList').empty();
                var postalCode = $('#postalCode').val();
                var upc = $('#productID').val();

                markers.forEach(function (marker) {
                    marker.setMap(null);
                });
                markers = [];

                $.getJSON( '<?php echo dirname( plugin_dir_url( __FILE__ ) ); ?>/locator-service.php?location=1&brandid=<?php echo $brand_id; ?>&clientid=<?php echo $client_id; ?>&radius=<?php echo $radius; ?>&upc=' + $('#productID').val() + "&zip=" + $('#postalCode').val(), function( data ) {
                    console.log(data);
                    if(data != "No Stores") {
                        var points = new Array();

                        //console.log(data);
                        $.each( data, function (key, val){
                            if (key == 'STORES') {
                                $.each(val, function(index, element){
                                    if(index == 'STORE') {
                                        $.each(element, function(store, attr){
                                            $('<div class="location"><span class="distance">' + attr['DISTANCE'] + ' miles' + '</span><h4 class="location-title">' + attr['NAME'] + '</h4><p><span class="address">' + attr['ADDRESS'] + ' ' + attr['CITY'] + ', ' + attr['STATE'] + ' ' + attr['ZIP'] + '</span><span class="phone">' + attr['PHONE'] + '</span></p></div>').appendTo('#locationsList');
                                            var point = [attr['NAME'], attr['ADDRESS'], attr['CITY'] + ', ' + attr['STATE'] + ' ' + attr['ZIP'], attr['DISTANCE'] + ' miles', attr['PHONE'], attr['LATITUDE'], attr['LONGITUDE']];
                                            points.push(point);
                                        });
                                    }
                                });
                            }
                        });

                        jQuery.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address=' + $('#postalCode').val() + '&key=<?php echo $google_maps_api_key; ?> ', null, function (zipData) {
                            //console.log(zipData);
                            var p = zipData.results[0].geometry.location;
                            //var centerMap = new google.maps.LatLng(p.lat, p.lng);

                            newLocation(p.lat, p.lng);

                            function newLocation(newLat, newLng) {
                                map.setCenter({
                                    lat: newLat,
                                    lng: newLng
                                });
                                //console.log(points);
                                var locations = points;
                                for (i = 0; i < locations.length; i++) {
                                    if (locations[i][0] =='undefined'){ storeName ='';} else { storeName = locations[i][0];}
                                    if (locations[i][1] =='undefined'){ storeAddress ='';} else { storeAddress = locations[i][1];}
                                    if (locations[i][2] =='undefined'){ storeCity ='';} else { storeCity = locations[i][2];}
                                    if (locations[i][3] =='undefined'){ storeDistance ='';} else { storeDistance = locations[i][3];}
                                    if (locations[i][4] =='undefined'){ storePhone ='';} else { storePhone = locations[i][4];}
                                    //var newGoogleTest = new google.maps.LatLng(locations[i][5], locations[i][6]);
                                    console.log(locations[i][5]);
                                    marker = new google.maps.Marker({
                                        position: new google.maps.LatLng(locations[i][5], locations[i][6]),
                                        map: map,
                                        title: 'Store Information',
                                        desc: storeName,
                                        addr: storeAddress,
                                        city: storeCity,
                                        distance: storeDistance,
                                        phone: storePhone
                                    });
                                    bindInfoWindow(marker, map, storeName, storeAddress, storeCity, storeDistance, storePhone);
                                    markers.push(marker);
                                }
                                function bindInfoWindow(marker, map, storeName, storeAddress, storeCity, storeDistance, storePhone) {
                                    var infoWindowVisible = (function () {
                                        var currentlyVisible = false;
                                        return function (visible) {
                                            if (visible !== undefined) {
                                                currentlyVisible = visible;
                                            }
                                            return currentlyVisible;
                                        };
                                    }());
                                    iw = new google.maps.InfoWindow();
                                    google.maps.event.addListener(marker, 'click', function() {
                                        if (infoWindowVisible()) {
                                            iw.close();
                                            infoWindowVisible(false);
                                        } else {
                                            var html= "<div class='marker-info'><h4>"+storeName+"</h4><p><span class='address'>"+storeAddress+"</span><span class='city'>"+storeCity+"</span><span class='phone'>"+storePhone+"</span><span class='distance'>"+storeDistance+"</span></p></div>";
                                            iw = new google.maps.InfoWindow({content:html});
                                            iw.open(map,marker);
                                            infoWindowVisible(true);
                                        }
                                    });
                                    google.maps.event.addListener(iw, 'closeclick', function () {
                                        infoWindowVisible(false);
                                    });
                                }
                            }
                        });
                        $('#noLocationsFoundText').attr('hidden', '');
                        $( '#locationsList' ).show();
                        $('#locations').removeAttr('hidden');
                        $('#locationsHeading').removeAttr('hidden');
                    } else {
                        //Remove markers  on reload
                        //marker.setMap(null);
                        //marker.setVisible(false);
                        $('#productNotFoundName').text($("#productID option:selected").text());
                        $('#productNotFoundZip').text($("#postalCode").val());
                        console.log("No locations found");
                        $( '#locationsList' ).hide();
                        $('#locations, #noLocationsFoundText').removeAttr('hidden');
                        $('#locationsHeading').attr('hidden', '');
                    }

                    $('.search.locations').show().next('.loading').attr('hidden', '');
                });
            });
        });
    </script>
    <?php
}
