/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'uiClass',
    'uiRegistry'
], function ($, Class, registry) {
    'use strict';

    var specificCountry = window.checkoutConfig.oscConfig.autocomplete.google_default_country,
        elementFields = ['street', 'country_id', 'city', 'region', 'region_id', 'region_id_input', 'postcode'];
    var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        neighborhood: 'long_name',
        administrative_area_level_2: 'short_name',
        locality: 'long_name',
        administrative_area_level_1: 'long_name',
        country: 'short_name',
        postal_code: 'short_name'
    };
    var isUsedMaterialDesign = window.checkoutConfig.oscConfig.isUsedMaterialDesign;

    return Class.extend({
        initialize: function (fieldsetName) {
            this._super();

            this.initAddressElements(fieldsetName)
                .initAutoComplete();

            return this;
        },
        initAddressElements: function (fieldsetName) {
            var self = this;

            this.addressElements = {};

            $.each(elementFields, function (index, field) {
                registry.async(fieldsetName + '.' + field)(function (fieldElement) {
                    if (field === 'street') {
                        $.each(fieldElement.elems(), function (key, elem) {
                            if (key === 0) {
                                fieldElement           = elem;
                                self.inputSelector     = document.getElementById(elem.uid);
                                self.geolocateSelector = $('#' + elem.uid + '-geolocation');
                            }
                        });
                    }
                    if (typeof fieldElement !== 'undefined') {
                        self.addressElements[field] = fieldElement;
                    }
                });
            });

            return this;
        },
        initAutoComplete: function () {
            if (this.inputSelector) {
                var options = {
                    types: ['geocode']
                };
                if (specificCountry) {
                    options.componentRestrictions = {country: specificCountry};
                }

                this.autoComplete = new google.maps.places.Autocomplete(this.inputSelector, options);
                if (isUsedMaterialDesign) {
                    $(this.inputSelector).attr('placeholder', '');
                }

                this.autoComplete.addListener('place_changed', this.placeChangedListener.bind(this));

                //if(!specificCountry) {
                //    this.geolocate();
                //}

                if (this.geolocateSelector.length) {
                    this.geolocateSelector.on('click', this.getCurrentLocation.bind(this));
                }
            }

            return this;
        },
        placeChangedListener: function () {
            var place = this.autoComplete.getPlace();

            this.unserializeAddress(place);
        },

        unserializeAddress: function (place) {
            var responseComponents = this.initResponseComponents();

            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentForm.hasOwnProperty(addressType)) {
                    var addressValue = place.address_components[i][componentForm[addressType]];

                    if ($.inArray(addressType, ['street_number', 'route', 'administrative_area_level_2']) !== -1) {
                        if (responseComponents.street != '') {
                            responseComponents.street += ', ';
                        }
                        responseComponents.street += addressValue;
                    }
                    if (addressType == 'locality') {
                        responseComponents.city = addressValue;
                    }
                    if (addressType == 'administrative_area_level_1') {
                        responseComponents.region = addressValue;
                        responseComponents.region_id = addressValue;
                        responseComponents.region_id_input = addressValue;
                    }
                    if (addressType == 'country') {
                        responseComponents.country_id = addressValue;
                    }
                    if (addressType == 'postal_code') {
                        responseComponents.postcode = addressValue;
                    }
                }
            }
            if (place.hasOwnProperty('name')) {
                responseComponents.street = place.name;
            }

            this.fillInAddress(responseComponents);
        },

        fillInAddress: function (components) {
            var self = this;
            $.each(this.addressElements, function (index, element) {
                if (element.visible() && components.hasOwnProperty(index)) {
                    if (index == 'region_id') {
                        $.each(element.options(), function (key, option) {
                            if (components[index] == option.label) {
                                element.value(option.value);
                                return false;
                            }
                        });
                    } else {
                        element.value(components[index]);
                        if (index == 'street') {
                            self.inputSelector.value = components[index];
                        }
                    }
                }
            });
        },

        /**
         * Bias the autocomplete object to the user's geographical location
         */
        geolocate: function () {
            var self = this;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var geolocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    var circle = new google.maps.Circle({
                        center: geolocation,
                        radius: position.coords.accuracy
                    });
                    self.autoComplete.setBounds(circle.getBounds());
                });
            }
        },

        getCurrentLocation: function () {
            var self = this;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var geocoder = new google.maps.Geocoder();
                    var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    geocoder.geocode({'latLng': location}, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            self.unserializeAddress(results[0]);
                        } else {
                            return false;
                        }
                    });
                });
            }
        },

        initResponseComponents: function () {
            return {
                street: '',
                country_id: '',
                region: '',
                region_id: '',
                region_id_input: '',
                city: '',
                postcode: ''
            };
        }
    });
});