/**
 * Address field with Google Places autocomplete.
 *
 * Extends the core address field. In edit mode it attaches a Google Places
 * Autocomplete widget to the Street input; picking a suggestion fills in
 * Street / City / State / Postal Code / Country. The Google Maps JavaScript
 * API is loaded once on demand using the instance's `googleMapsApiKey` config
 * value (the same key the core address map preview uses).
 *
 * Wired globally via metadata/fields/address.json, so every address-type field
 * (Account billingAddress, Lead address, Opportunity propAddress, etc.) gets it.
 * If no key is configured or the library fails to load, the field behaves
 * exactly like the stock address field — autocomplete is purely additive.
 */
define('custom:views/fields/address', ['views/fields/address'], function (Dep) {

    return Dep.extend({

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            if (this.isEditMode()) {
                this.initAddressAutocomplete();
            }
        },

        initAddressAutocomplete: function () {
            var apiKey = this.getConfig().get('googleMapsApiKey');

            if (!apiKey) {
                // No key configured — leave the plain address field untouched.
                return;
            }

            var $street = this.getSubInput('Street');

            if (!$street.length) {
                return;
            }

            this.loadGooglePlaces(apiKey).then(function () {
                // The view may have re-rendered (mode switch) before the script
                // finished loading; only attach if this input is still in the DOM.
                if (!this.isEditMode() || !$street.closest('html').length) {
                    return;
                }

                if ($street.data('placesAttached')) {
                    return;
                }

                $street.data('placesAttached', true);

                this.attachAutocomplete($street.get(0));
            }.bind(this)).catch(function () {
                // Maps API failed to load (bad key, Places API disabled, offline).
                // Silently fall back to the stock field.
            });
        },

        attachAutocomplete: function (streetInputEl) {
            var google = window.google;

            if (!google || !google.maps || !google.maps.places) {
                return;
            }

            var autocomplete = new google.maps.places.Autocomplete(streetInputEl, {
                types: ['address'],
                fields: ['address_components']
            });

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();

                if (!place || !place.address_components) {
                    return;
                }

                this.applyPlace(place.address_components);
            }.bind(this));

            // Stop the Enter key (used to pick a suggestion) from submitting the
            // surrounding edit form / record.
            this.$el.on('keydown.placesAutocomplete', '[data-name="' + this.name + 'Street"]', function (e) {
                if (e.keyCode === 13) {
                    var pacOpen = window.document.querySelector('.pac-container[style*="display: block"]') ||
                        window.document.querySelector('.pac-item-selected');

                    if (pacOpen) {
                        e.preventDefault();
                    }
                }
            });
        },

        applyPlace: function (components) {
            var get = function (type, useShort) {
                var c = components.find(function (comp) {
                    return comp.types.indexOf(type) !== -1;
                });

                if (!c) {
                    return '';
                }

                return useShort ? c.short_name : c.long_name;
            };

            var streetNumber = get('street_number');
            var route = get('route');
            var street = [streetNumber, route].filter(Boolean).join(' ');

            var city = get('locality') || get('postal_town') ||
                get('sublocality_level_1') || get('administrative_area_level_2');

            var state = get('administrative_area_level_1', true);
            var postalCode = get('postal_code');
            var country = get('country');

            this.setSubValue('Street', street);
            this.setSubValue('City', city);
            this.setSubValue('State', state);
            this.setSubValue('PostalCode', postalCode);
            this.setSubValue('Country', country);

            this.trigger('change');
        },

        getSubInput: function (part) {
            var $byDataName = this.$el.find('[data-name="' + this.name + part + '"]');

            if ($byDataName.length) {
                return $byDataName;
            }

            // Fallback for template variations that key inputs by suffix.
            return this.$el.find('input[data-name$="' + part + '"], input[name$="' + part + '"]').first();
        },

        setSubValue: function (part, value) {
            if (value === '' || value === null || value === undefined) {
                // Don't blank out a subfield the place didn't include (e.g. no
                // suite/unit) — keep whatever the user already typed.
                return;
            }

            var attribute = this.name + part;
            var $input = this.getSubInput(part);

            if ($input.length) {
                $input.val(value);
                // Notify any DOM listeners the core view may have bound.
                $input.get(0).dispatchEvent(new Event('change', {bubbles: true}));
            }

            this.model.set(attribute, value, {ui: true});
        },

        loadGooglePlaces: function (apiKey) {
            if (window.google && window.google.maps && window.google.maps.places) {
                return Promise.resolve();
            }

            if (window.__rsgGmapsPromise) {
                return window.__rsgGmapsPromise;
            }

            window.__rsgGmapsPromise = new Promise(function (resolve, reject) {
                window.__rsgGmapsInit = function () {
                    resolve();
                };

                var script = window.document.createElement('script');

                script.src = 'https://maps.googleapis.com/maps/api/js?key=' +
                    encodeURIComponent(apiKey) +
                    '&libraries=places&loading=async&callback=__rsgGmapsInit';
                script.async = true;
                script.defer = true;
                script.onerror = function () {
                    window.__rsgGmapsPromise = null;
                    reject(new Error('Failed to load Google Maps JavaScript API'));
                };

                window.document.head.appendChild(script);
            });

            return window.__rsgGmapsPromise;
        },

        onRemove: function () {
            this.$el.off('keydown.placesAutocomplete');

            Dep.prototype.onRemove.call(this);
        }
    });
});
