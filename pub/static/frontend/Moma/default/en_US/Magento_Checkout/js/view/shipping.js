/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'twzipcode',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service'
    ],
    function (
        $,
        Twzipcode,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t
    ) {
        'use strict';

        var popUp = null;
        var count=0;


        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping'
            },
            visible: ko.observable(!quote.isVirtual()),
            errorValidationMessage: ko.observable(false),
            isCustomerLoggedIn: customer.isLoggedIn,
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: 1,
            quoteIsVirtual: quote.isVirtual(),

            /**
             * @return {exports}
             */
            initialize: function () {

                var self = this,
                    hasNewAddress,
                    fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';
                     
                this._super();
                shippingRatesValidator.initFields(fieldsetName);

                if (!quote.isVirtual()) {
                    stepNavigator.registerStep(
                        'shipping',
                        '',
                        $t('Shipping'),
                        this.visible, _.bind(this.navigate, this),
                        10
                    );
                }
                checkoutDataResolver.resolveShippingAddress();

                hasNewAddress = addressList.some(function (address) {
                    return address.getType() == 'new-customer-address';
                });

                this.isNewAddressAdded(hasNewAddress);

                this.isFormPopUpVisible.subscribe(function (value) {
                    if (value) {
                        self.getPopUp().openModal();
                    }
                });

                quote.shippingMethod.subscribe(function () {
                    self.errorValidationMessage(false);
                });

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                });



                // ('.action-show-popup-wrap').addClass('hideaddresscvs');

                $('select[name="billing_address_id"]').on('change', function() {
                        // $('.action-update').click().change();
                        var a=$('select[name="billing_address_id"] option:selected').html();
                        if(a === '新增地址' || a === 'New Address'){
                            // console.log('111');
                            $(".action-update").removeClass("removebuttonupdate").change();
                        }else{
                            //console.log('2222');
                             $(".action-update").addClass("removebuttonupdate").change();
                             $('.action-update').click().change();
                        }
                    })
                $('.close-billing-popup').click(function(){
                    $(".action-update").addClass("removebuttonupdate").change();
                });
                 var checked = $("#s_method_collect_storecvs_collect_storecvs").is(":checked");
                 var value = $.cookie('shippingcvs');
                 // alert(value);
                 var street = $("input[name='street[0]']").val();
                 var tel = $("input[name='telephone']").val();
                 var company = $("input[name='company']").val();
                 var standalone = window.navigator.standalone,
                    userAgent = window.navigator.userAgent.toLowerCase(),
                    safari = /safari/.test( userAgent ),
                    ios = /iphone|ipod|ipad/.test( userAgent );

                        if( ios ) {
                            if ( !standalone && safari ) {
                        
                            } else if ( standalone && !safari ) {
                               
                            } else if ( !standalone && !safari ) {
                                localStorage.setItem('doanh','aaaa');
                            };
                        }
                        var pathname = window.location.href;
                return this;
            },

            /**
             * Load data from server for shipping step
             */
            navigate: function () {
                //load data from server for shipping step
            },

            /**
             * @return {*}
             */
            getPopUp: function () {
                var self = this,
                    buttons;
                $('#twzipcode').twzipcode({
                    'countyName'   : 'citynew',
                    'districtName' : 'regionnew',
                    'zipcodeName'  : 'postcodenew',
                    'readonly'     : true
                });
                $('select[name="citynew"]').on('change', function() {
                    $('#co-shipping-form input[name="city"]').val(this.value).change();
                    $('input[name="region"]')[1].value = $('select[name="regionnew"]').val();
                    $('input[name="postcode"]')[1].value = $('input[name="postcodenew"]').val();

                    if (!this.value) {
                        $('.mage-error-shipping-city').addClass('active');
                        $('.mage-error-shipping-region').addClass('active');
                        $('.mage-error-shipping-postcode').addClass('active');
                    } else {
                        $('.mage-error-shipping-city').removeClass('active');
                        $('.mage-error-shipping-region').removeClass('active');
                        $('.mage-error-shipping-postcode').removeClass('active');
                    }
                });
                $('select[name="regionnew"]').on('change', function() {
                    $('#co-shipping-form input[name="region"]').val($('select[name="regionnew"]').val()).change();
                    $('input[name="postcode"]')[1].value = $('input[name="postcodenew"]').val();
                })


                if (!popUp) {
                    buttons = this.popUpForm.options.buttons;
                    this.popUpForm.options.buttons = [
                        {
                            text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                            class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                            click: self.saveNewAddress.bind(self)
                        },
                        {
                            text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                            class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',
                            click: function () {
                                this.closeModal();
                            }
                        }
                    ];
                    this.popUpForm.options.closed = function () {
                        self.isFormPopUpVisible(false);
                    };
                    popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
                }

                return popUp;
            },

            /**
             * Show address form popup
             */
            showFormPopUp: function () {
                this.isFormPopUpVisible(true);
            },

            /**
             * Save new shipping address
             */
            saveNewAddress: function () {
                var addressData,
                    newShippingAddress;
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                var city = $('select[name="citynew"]').val();

                if (!city) {
                    $('.mage-error-shipping-city').addClass('active');
                    $('.mage-error-shipping-region').addClass('active');
                    $('.mage-error-shipping-postcode').addClass('active');
                } else {
                    $('.mage-error-shipping-city').removeClass('active');
                    $('.mage-error-shipping-region').removeClass('active');
                    $('.mage-error-shipping-postcode').removeClass('active');
                }

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get('shippingAddress');
                    // if user clicked the checkbox, its value is true or false. Need to convert.
                    addressData.save_in_address_book = this.saveInAddressBook ? 1 : 0;

                    // New address must be selected as a shipping address
                    addressData.city = $('input[name="city"]')[1].value ;
                    addressData.region = $('input[name="region"]')[1].value;
                    addressData.postcode = $('input[name="postcode"]')[1].value;
                    $('input[name="billing-address-same-as-shipping"]').prop("checked", false);
                    newShippingAddress = createShippingAddress(addressData);
                    selectShippingAddress(newShippingAddress);
                    checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                    checkoutData.setNewCustomerShippingAddress(addressData);
                    this.getPopUp().closeModal();
                          this.isNewAddressAdded(false);
                 
                                        var a= localStorage.getItem('doanh');

                    if(a !== null){
                        console.log('not null');
                        this.isFormPopUpVisible(false);
                    }else{
                        console.log('null');
                           this.isFormPopUpVisible(true);
                    }
                    $('#opc-new-shipping-address').show();
                    



                }
            },
            // $("body").delegate("#s_method_collect_store_collect_store", "click", function() {
            //   var a= localStorage.getItem('doanh');

            // });
            /**
             * Shipping Method View
             */
            rates: shippingService.getShippingRates(),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                console.log(quote.shippingMethod());
                    var standalone = window.navigator.standalone,
                    userAgent = window.navigator.userAgent.toLowerCase(),
                    safari = /safari/.test( userAgent ),
                    ios = /iphone|ipod|ipad/.test( userAgent );
                    if( ios ) {
                        if ( !standalone && safari ) {
                         
                        } else if ( standalone && !safari ) {
                           
                        } else if ( !standalone && !safari && count === 0 ) {
                            //alert('11');
                            count++;
                            
                                return 'collect_storecvs_collect_storecvs';
                            
                        };
                    } else {
                        // alert('not ios');
                    }
                    return quote.shippingMethod() ?
                        quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
            
                }
            ),

            /**
             * @param {Object} shippingMethod
             * @return {Boolean}
             */
            selectShippingMethod: function (shippingMethod) {
                var self = this;
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);
                console.log(shippingMethod);
                if(shippingMethod.method_code === 'collect_store'){
                    var a= localStorage.getItem('doanh');
                   // isNewAddressAdded=ko.observable(false);
                }
                return true;
            },

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                if (this.validateShippingInformation()) {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var shippingAddress,
                    addressData,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage($.mage.__('Please specify a shipping method.'));

                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');

                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if (this.source.get('params.invalid') ||
                        !quote.shippingMethod().method_code ||
                        !quote.shippingMethod().carrier_code ||
                        !emailValidationResult
                    ) {
                        return false;
                    }

                    shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );

                    //Copy form data to quote shipping address object
                    for (var field in addressData) {

                        if (addressData.hasOwnProperty(field) &&
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = 1;
                    }
                    selectShippingAddress(shippingAddress);
                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();

                    return false;
                }

                return true;
            }
        });
    }
);

