/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Customer/js/model/customer',
        'mage/validation'
    ],
    function ($, customer) {
        'use strict';

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                var typeInvoice =jQuery('[name="invoice"]:checked').val();
                if(typeInvoice === 'three'){
                  
                    var a = jQuery('[name="invoice-select"]').val();
                    if(a === '0'){
                         $('#err').hide();
                    $('#err').html('<div>必填欄位</div>');
                    $('#err').show();
                        return false;    
                    }
                    

                }else{
                    var b = jQuery('[name="purchaser-name"]').val();
                    if(b !== ''){
                        if(!($.isNumeric(b) && b.length == 8)){
                        $('#err').hide();
                        $('#err').html('<div>統一編號需要有8碼</div>');
                        $('#err').show();
                        return false;
            }
                    }
                }
        }
    }
}
    
);
