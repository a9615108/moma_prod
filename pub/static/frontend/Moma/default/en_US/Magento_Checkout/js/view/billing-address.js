/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'ko',
        'underscore',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'jquery'
    ],
    function (
        ko,
        _,
        Component,
        customer,
        addressList,
        quote,
        createBillingAddress,
        selectBillingAddress,
        checkoutData,
        checkoutDataResolver,
        customerData,
        setBillingAddressAction,
        globalMessageList,
        $t,
        $
    ) {
        'use strict';

        var lastSelectedBillingAddress = null,
            newAddressOption = {
                /**
                 * Get new address label
                 * @returns {String}
                 */
                getAddressInline: function () {
                    return $t('New Address');
                },
                customerAddressId: null
            },
            countryData = customerData.get('directory-data'),
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address';
            });

        addressOptions.push(newAddressOption);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },
            currentBillingAddress: quote.billingAddress,
            addressOptions: addressOptions,
            customerHasAddresses: addressOptions.length > 1,

            /**
             * Init component
             */
            initialize: function () {
                this._super();
                quote.paymentMethod.subscribe(function () {
                    checkoutDataResolver.resolveBillingAddress();
                }, this);
            },

            /**
             * @return {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: quote.billingAddress() != null,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length == 1,
                        isAddressSameAsShipping: false,
                        saveInAddressBook: 1
                    });
                $(document).keyup(function(e) {
                    if (e.keyCode === 27) {
                        var body = $('body');
                        if (body.hasClass('popup-billing')) {
                            body.removeClass('popup-billing');
                        }
                    }
                });

                quote.billingAddress.subscribe(function (newAddress) {
                    if (quote.isVirtual()) {
                        this.isAddressSameAsShipping(false);
                    } else {
                        this.isAddressSameAsShipping(
                            newAddress != null &&
                            newAddress.getCacheKey() == quote.shippingAddress().getCacheKey()
                        );
                    }

                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                    } else {
                        this.saveInAddressBook(1);
                    }
                    this.isAddressDetailsVisible(true);
                }, this);

                return this;
            },

            canUseShippingAddress: ko.computed(function () {
                return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
            }),

            /**
             * @param {Object} address
             * @return {*}
             */
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },

            /**
             * @return {Boolean}
             */
            useShippingAddress: function () {
                if (this.isAddressSameAsShipping()) {
                    selectBillingAddress(quote.shippingAddress());

                    this.updateAddresses();
                    this.isAddressDetailsVisible(true);
                } else {
                    lastSelectedBillingAddress = quote.billingAddress();
                    quote.billingAddress(null);
                    this.isAddressDetailsVisible(false);
                }
                checkoutData.setSelectedBillingAddress(null);

                return true;
            },

            /**
             * Update address action
             */
            updateAddress: function () {
                if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                    selectBillingAddress(this.selectedAddress());
                    checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
                } else {

                    this.source.set('params.invalid', false);
                    this.source.trigger(this.dataScopePrefix + '.data.validate');

                    if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                        this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                    }

                    var city = $('select[name="billingcitynew"]').val();

                    if (!city) {
                        $('.mage-error-billing-city').addClass('active');
                        $('.mage-error-billing-region').addClass('active');
                        $('.mage-error-billing-postcode').addClass('active');
                    } else {
                        $('.mage-error-billing-city').removeClass('active');
                        $('.mage-error-billing-region').removeClass('active');
                        $('.mage-error-billing-postcode').removeClass('active');
                    }

                    if (!this.source.get('params.invalid')) {
                        if ($('body').hasClass('popup-billing')) {
                            $('body').removeClass('popup-billing');
                        }
                        var addressData = this.source.get(this.dataScopePrefix),
                            newBillingAddress;

                        if (customer.isLoggedIn() && !this.customerHasAddresses) {
                            this.saveInAddressBook(1);
                        }
                        addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                        addressData['city'] = $('input[name="city"]')[0].value;
                        addressData['region'] = $('input[name="region"]')[0].value;
                        addressData['postcode'] = $('input[name="postcode"]')[0].value;
                        newBillingAddress = createBillingAddress(addressData);

                        // New address must be selected as a billing address
                        selectBillingAddress(newBillingAddress);
                        checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                        checkoutData.setNewCustomerBillingAddress(addressData);
                    }
                }
                this.updateAddresses();
            },

            /**
             * Edit address action
             */
             editAddress: function () {
                lastSelectedBillingAddress = quote.billingAddress();
                if (!lastSelectedBillingAddress.customerAddressId) {
                    if (!$('body').hasClass('popup-billing')) {
                        $('body').addClass('popup-billing');
                    }
                }
                quote.billingAddress(null);
                this.isAddressDetailsVisible(false);
            },

            /**
             * Cancel address edit action
             */
            cancelAddressEdit: function () {
                this.restoreBillingAddress();

                if (quote.billingAddress()) {
                    // restore 'Same As Shipping' checkbox state
                    this.isAddressSameAsShipping(
                        quote.billingAddress() != null &&
                        quote.billingAddress().getCacheKey() == quote.shippingAddress().getCacheKey() &&
                        !quote.isVirtual()
                    );
                    this.isAddressDetailsVisible(true);
                }
            },

            /**
             * Restore billing address
             */
            restoreBillingAddress: function () {
                if (lastSelectedBillingAddress != null) {
                    selectBillingAddress(lastSelectedBillingAddress);
                }
            },

            /**
             * @param {Object} address
             */
            onAddressChange: function (address) {
                if (address == newAddressOption) {
                    if (!$('body').hasClass('popup-billing')) {
                        $('body').addClass('popup-billing');
                    }
                }
                $('#billingtwzipcode').twzipcode({
                    'countyName'   : 'billingcitynew',
                    'districtName' : 'billingregionnew',
                    'zipcodeName'  : 'billingpostcodenew',
                    'readonly'     : true,
                });

                $('select[name="billingcitynew"]').on('change', function() {
                    $('#billing-new-address-form input[name="city"]').val(this.value).change();
                    $('input[name="region"]')[0].value = $('select[name="billingregionnew"]').val();
                    $('input[name="postcode"]')[0].value = $('input[name="billingpostcodenew"]').val();

                    if (!this.value) {
                        $('.mage-error-billing-city').addClass('active');
                        $('.mage-error-billing-region').addClass('active');
                        $('.mage-error-billing-postcode').addClass('active');
                    } else {
                        $('.mage-error-billing-city').removeClass('active');
                        $('.mage-error-billing-region').removeClass('active');
                        $('.mage-error-billing-postcode').removeClass('active');
                    }
                });

                $('select[name="billingregionnew"]').on('change', function() {
                    $('#billing-new-address-form input[name="region"]').val($('select[name="billingregionnew"]').val()).change();
                    $('input[name="postcode"]')[0].value = $('input[name="billingpostcodenew"]').val();
                })
                $('.close-billing-popup').on('click', function() {
                    $('body').removeClass('popup-billing');
                })
                this.isAddressFormVisible(address == newAddressOption);
            },

            /**
             * @param {int} countryId
             * @return {*}
             */
            getCountryName: function (countryId) {
                return countryData()[countryId] != undefined ? countryData()[countryId].name : '';
            },

            /**
             * Trigger action to update shipping and billing addresses
             */
            updateAddresses: function () {
                if (window.checkoutConfig.reloadOnBillingAddress ||
                    !window.checkoutConfig.displayBillingOnPaymentMethod
                ) {
                    setBillingAddressAction(globalMessageList);
                }
            },

            /**
             * Get code
             * @param {Object} parent
             * @returns {String}
             */
            getCode: function (parent) {
                return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
            }
        });
    }
);
