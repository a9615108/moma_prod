/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_SalesRule/js/action/set-coupon-code',
        'Magento_SalesRule/js/action/cancel-coupon'
    ],
    function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction) {
        'use strict';
        var firstNewInvoice = true;
        var show_hide_taxid_blockConfig = window.checkoutConfig.show_hide_taxid_block;
        //var typeIdHistoryList = typeIdHistory;
        //var typeIdHistoryOb = ko.observableArray(typeIdHistoryList);
        //var selectedInvoice = ko.observable();
        var newTaxId = ko.observable();
        var newPurChaserName = ko.observable();

        //trigger show when new invoice type is chosen
       // var selectOptionNew = ko.computed(function() {
       //     if(selectedInvoice()){
        //        $('#err4').hide();
         //       return false;
        //    }
        //    return true;
       // });
        var showTaxIdBlockObserver = ko.observable(false);

        //tringer show three option is selected
        var changeInput = function onInputChange(item, data) {
            var element = data.target
            if (element.value === 'three'){
                showTaxIdBlockObserver(true);
            }
            else showTaxIdBlockObserver(false);
        }

        var storeNewInvoice = function () {
            //validate input field is filled
            if (!newTaxId()){
                $('#err1').show();
            }else {
                $('#err1').hide();
            }
            if (!newPurChaserName()){
                $('#err2').show();
            }else {
                $('#err2').hide();
            }

            if (newTaxId() && newPurChaserName()){
                if (firstNewInvoice === true){
                    typeIdHistoryOb.push({'tax_id':newTaxId,'purchaser_name':newPurChaserName});
                    firstNewInvoice = false;
                }else {
                    typeIdHistoryOb.pop();
                    typeIdHistoryOb.push({'tax_id':newTaxId,'purchaser_name':newPurChaserName});
                }
                $('#err4').hide();
            }
        }

        return Component.extend({
            defaults: {
                template: 'Astralweb_Invoicetype/taxid'
            },
            canVisibleBlock:show_hide_taxid_blockConfig,
            //typeIdHistoryOb:typeIdHistoryOb,
            //selectedInvoice:selectedInvoice,
           // typeIdHistoryList:typeIdHistoryList,
            //selectOptionNew:selectOptionNew,
            showTaxIdBlockObserver:showTaxIdBlockObserver,
            changeInput:changeInput,
            newTaxId:newTaxId,
            newPurChaserName:newPurChaserName,
            //storeNewInvoice:storeNewInvoice
        });
    }
);