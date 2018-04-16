/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Astralweb_Invoicetype/js/model/validator-invoicetype'
    ],
    function (Component, additionalValidators, validatorInvoicetype) {
        'use strict';
        additionalValidators.registerValidator(validatorInvoicetype);
        return Component.extend({});
    }
);