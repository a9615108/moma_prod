/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (placeOrderAction) {
        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function(originalAction, paymentData, redirectOnSuccess, messageContainer) {
            // adding tax id info
            var show_hide_taxid_blockConfig = window.checkoutConfig.show_hide_taxid_block;
           
                var typeInvoice =jQuery('[name="invoice"]:checked').val();
                paymentData.additional_data ={};
                paymentData.additional_data.type = typeInvoice;

                if(typeInvoice === 'two'){
                     var name = jQuery('[name="purchaser-name"]').val();
                     console.log(name);
                   paymentData.additional_data.purchaser_name = name;

                }else{
                   var tax = jQuery('[name="invoice-select"]').val();
                   var texttax = jQuery("[name='invoice-select'] option:selected").text();
                    //console.log('oldId:'+oldId+':tax:'+tax+':name:'+name);
                   // paymentData.additional_data.old_id = oldId;
                    paymentData.additional_data.tax_id = tax+','+texttax;
                   // alert(paymentData);
                }
            
            return originalAction(paymentData, redirectOnSuccess, messageContainer);
        });
    };
});