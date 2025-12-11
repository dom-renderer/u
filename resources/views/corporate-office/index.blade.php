@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />

    <style type="text/css">
        #map {
            height: 650px;
            width: 100%;
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
            background-color: #fff;
            border: 0;
            border-radius: 2px;
            box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
            margin: 10px;
            padding: 0 0.5em;
            font: 400 18px Roboto, Arial, sans-serif;
            overflow: hidden;
            font-family: Roboto;
            padding: 0;
        }

        #pac-container {
            padding-bottom: 12px;
            margin-right: 12px;
            z-index: 99999;
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
            position: absolute;
            top: 11px;
            height: 40px;
            left: 188px;
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

        div[id^=map_canvas],
        div[id^=map_canvas] div {
            overflow: auto;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .pac-container {
            background-color: #FFF;
            z-index: 2000;
            position: fixed;
            display: inline-block;
        }

        .select2-container .select2-search--inline .select2-search__field {
            height: 20px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 37px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }    
        
        .select2-container {
            background: none;
            border: none;
        }        
    </style>
@endpush

@section('content')

    <div class="row">
        <div class="col-3 col-xl-3 col-lg-5 col-border">

			<div class="col-title mb-30">
                <h2>Add Office</h2>
            </div>
            <div class="fursa-form">
					<form method="POST" action="{{ route('corporate-office.store') }}" class="gift-submit-form"> @csrf

						<input name="location" id="location" type="hidden">

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="locname"> Name </label>
                                    <input name="name" type="text" class="form-control" id="" placeholder="Branch Name" required ></div>
                            </div>
                        </div>

						@if ($errors->has('name'))
							<span class="text-danger text-left">{{ $errors->first('name') }}</span>
						@endif

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="searchTextField"> Address Line 1 </label>
                                    <input name="address1" type="text" class="form-control"
                                        id="searchTextField" placeholder="Address Line 1" required></div>
                            </div>
                        </div>

						@if ($errors->has('address1'))
							<span class="text-danger text-left">{{ $errors->first('address1') }}</span>
						@endif

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="addrln2"> Address Line 2 </label>
                                    <input name="address2" type="text" class="form-control" id="" placeholder="Address Line 2" required></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-6">
                                    <label class="form-label" for="block"> Block </label>
                                    <input name="block" type="text" class="form-control" placeholder="Block" required >
									@if ($errors->has('block'))
										<span class="text-danger text-left">{{ $errors->first('block') }}</span>
									@endif
								</div>
                                <div class="col-6">
                                    <label class="form-label" for="street"> Street </label>
                                    <input name="street" type="text" class="form-control" placeholder="Street" required >
									@if ($errors->has('street'))
										<span class="text-danger text-left">{{ $errors->first('street') }}</span>
									@endif
								</div>
                            </div>
                        </div>
                       
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="landmark"> Landmark </label>
                                    <input name="landmark" type="text" class="form-control" placeholder="Landmark" required >
									@if ($errors->has('landmark'))
										<span class="text-danger text-left">{{ $errors->first('landmark') }}</span>
									@endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="location_url"> Location URL </label>
                                    <div class="input-group mb-3">
                                        <input name="location_url" type="text" class="form-control" id="location_url"
                                            placeholder="Location URL" required onkeydown="return false;" style="caret-color: transparent !important;" />
                                        <span class="input-group-text cursor-pointer" id="map-location"
                                            data-bs-toggle="modal" data-bs-target="#locationURLMap"><i
                                                class="bi bi-pin-map"></i></span>
                                    </div>

									@if ($errors->has('location_url'))
										<span class="text-danger text-left">{{ $errors->first('location_url') }}</span>
									@endif

                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="map_latitude"> Latitude </label>
                                    <input name="map_latitude" type="text" class="form-control"
                                        id="map_latitude" placeholder="Map Latitude" required  onkeydown="return false;"
                                        style="caret-color: transparent !important;" />
									
										@if ($errors->has('map_latitude'))
											<span class="text-danger text-left">{{ $errors->first('map_latitude') }}</span>
										@endif
									</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="map_longitude"> Longitude </label>
                                    <input name="map_longitude" type="text" class="form-control"
                                        id="map_longitude" placeholder="Map Longitude" required
                                        onkeydown="return false;"
                                        style="caret-color: transparent !important;" />

										@if ($errors->has('map_longitude'))
											<span class="text-danger text-left">{{ $errors->first('map_longitude') }}</span>
										@endif

									</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="mobile"> Mobile Number </label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="prefix-mobile">+1</span>
                                        <input name="mobile" type="hidden" class="form-control" id="mobile" placeholder="Mobile" required >
                                        <input name="mobile_type" type="text" class="form-control" id="mobile_type" placeholder="Mobile" required>

										@if ($errors->has('mobile_type'))
											<span class="text-danger text-left">{{ $errors->first('mobile_type') }}</span>
										@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="whatsapp"> Whatsapp Number </label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="prefix-whatsapp">+1</span>
                                        <input name="whatsapp" type="hidden" class="form-control" id="whatsapp" placeholder="WhatsApp" required >
                                        <input name="whatsapp_type" type="text" class="form-control" id="whatsapp_type" placeholder="WhatsApp" required>

										@if ($errors->has('whatsapp_type'))
											<span class="text-danger text-left">{{ $errors->first('whatsapp_type') }}</span>
										@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="optime"> Opening Time </label>
                                    <input name="open_time" type="text" class="form-control timepicker" id="" placeholder="Opening Time" required >
									@if ($errors->has('open_time'))
										<span class="text-danger text-left">{{ $errors->first('open_time') }}</span>
									@endif
								</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label class="form-label" for="cltime"> Closing Time </label>
                                    <input name="close_time" type="text" class="form-control timepicker" id="" placeholder="Closing Time" required>
									@if ($errors->has('close_time'))
										<span class="text-danger text-left">{{ $errors->first('close_time') }}</span>
									@endif
								</div>
                            </div>
                        </div>
                      
						<button type="submit" class="btn btn-primary btn-fursa-form-submit"><i class="bi bi-plus-circle"></i> Save</button>

                    </form>
            </div>
        </div>

        <div class="col-9 col-xl-9 col-lg-7">
			<div class="col-title mb-30">
                <h2>Listed Office</h2>
            </div>
            <div class="listed-brances">
                <div class="row" id="brances_listed">

					@forelse ($stores as $branch)
                    <div class="col-lg-12 col-xl-4">
                        <div class="listing-box">
                            <p class="title main-title">{{ $branch->name }} {{ $branch->is_factory ? '(Inventory)' : '' }} </p>
                            <p class="description main-description">{{ $branch->address1 }}<br>
                                {{ $branch->address2 }}, {{ $branch->block }}, {{ $branch->street }}
                            </p>
                            <div class="wrapper">
                                <p class="title">Landmark:</p>
                                <p class="description">{{ $branch->landmark }}</p>
                            </div>
                            <div class="wrapper">
                                <p class="title">Mobile:</p>
                                <p class="description">{{ $branch->mobile }}</p>
                            </div>
                            <div class="wrapper">
                                <p class="title">Whatsapp:</p>
                                <p class="description">{{ $branch->whatsapp }}</p>
                            </div>
                            <div class="wrapper">
                                <p class="title">Location:</p>

                                <p class="description">
                                    @if ($branch->location && $branch->location != 'location')
                                        <a title="{{ $branch->location }}" target="_blank"
                                            href="{{ $branch->location_url ?? 'javascript:void(0);' }}">{{ Str::limit($branch->location, 12) }}</a>
                                    @else
                                        {{ $branch->location }}
                                    @endif
                                </p>
                            </div>
                            <div class="wrapper">
                                <p class="title">Opening Timing:</p>
                                <p class="description">{{ $branch->open_time }}</p>
                            </div>
                            <div class="wrapper">
                                <p class="title">Closing Timing:</p>
                                <p class="description">{{ $branch->close_time }}</p>
                            </div>

                            <hr>
                            
                            <div class="wrapper btn-wrapper">

                                <a style="margin-right:20px;" href="{{ route('corporate-office.edit', $branch->id) }}"><button type="button" class="btn btn-warning w-100"> Edit </button></a>
    
                                {!! Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['corporate-office.destroy', $branch->id],
                                    'style' => 'display:inline',
                                ]) !!}
    
                                <button style="margin-right:20px;" type="submit" class="btn btn-danger w-100 deleteGroup">Delete</button>
    
                                {!! Form::close() !!}

                            </div>
                        </div>
                    </div>
					@empty						
					@endforelse


                </div>
                <!-- Modal -->
                <div class="modal fade" id="locationURLMap" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="locationURLMapLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width:1700px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel"> Map </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row" id="location_url_map">
                                    <div class="col-12">
                                        <input id="pac-input" class="controls" type="text"
                                            placeholder="Search Box" />
                                        <div id="map"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="saveLocation">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD9o04kD0ZVh_Ks8tR_eWE5iztYxXqKlj8&libraries=places" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('gift-submit-form');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                            $(form).trigger('mdFormValidationErrors')
                        } else {
                            $(form).trigger('mdFormValidationSuccess')
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        jQuery(document).ready(function($) {

            $(".js-example-basic-multiple").select2({
                placeholder: "Select Item Category"
            });

            $('.deleteGroup').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this branch?')) {
                    e.preventDefault();
                }
            });

            $('.timepicker').timepicker({
                timeFormat: 'h:mm p',
                interval: 15,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });

            $('#mobile_type,#whatsapp').mask('0#');
            $('#mobile_type').on('input', function(e) {
                $("#mobile").val("965" + e.target.value);
                if (e.target.value.length < 8) {
                    this.setCustomValidity('Please enter a valid mobile number!');
                } else {
                    this.setCustomValidity('');
                }
            });
            $('#whatsapp_type').on('input', function(e) {
                $("#whatsapp").val("965" + e.target.value);
                if (e.target.value.length < 8) {
                    this.setCustomValidity('Please enter a valid whatsapp number!');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        function initialize() {
            var input = document.getElementById('searchTextField');
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    return;
                }
            });
        }

        let thisLat, thisLong, thisLatLongUrl, thePlaceName = null;

        function initAutocomplete() {
            const map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: -33.8688, lng: 151.2195 },
                zoom: 13,
                mapTypeId: "roadmap",
            });

            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);

            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
        });

        let marker = null;

        function createMarker(position, title) {
            if (marker) {
                marker.setMap(null);
            }

            const icon = {
                url: "{{ url('assets/images/markers.png') }}",
                scaledSize: new google.maps.Size(30, 30), 
            };

            marker = new google.maps.Marker({
                position,
                map,
                icon,
                title,
            });

            return marker;
        }

        function logPlaceDetails(position, name) {
            const lat = position.lat();
            const lng = position.lng();
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            console.log(`Latitude: ${lat}, Longitude: ${lng}, URL: ${url}`);

            thisLat = lat;
            thisLong = lng;
            thisLatLongUrl = url;
            thePlaceName = name;
        }

        map.addListener("click", (event) => {
            const position = event.latLng;
            createMarker(position, "");
            logPlaceDetails(position, "");
        });

        searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            const bounds = new google.maps.LatLngBounds();

            places.forEach((place) => {
                if (!place.geometry || !place.geometry.location) {
                    console.log("Returned place contains no geometry");
                    return;
                }

                const position = place.geometry.location;
                createMarker(position, place.name);
                logPlaceDetails(position, place.name);

                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(position);
                }
            });

            map.fitBounds(bounds);
        });
        }


        const locationURLMapModal = document.getElementById('locationURLMap');
        locationURLMapModal.addEventListener('shown.bs.modal', function(event) {
            initAutocomplete();

            document.getElementById('saveLocation').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('location').value = thePlaceName;
                document.getElementById('location_url').value = thisLatLongUrl;
                document.getElementById('map_latitude').value = thisLat;
                document.getElementById('map_longitude').value = thisLong;

                $(locationURLMapModal).modal('hide');
            }, false);
        })
    </script>
@endpush