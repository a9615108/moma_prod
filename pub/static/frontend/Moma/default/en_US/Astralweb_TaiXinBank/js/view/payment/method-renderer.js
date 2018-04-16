define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'taixinbank',
                component: 'Astralweb_TaiXinBank/js/view/payment/method-renderer/taixinbank'
            }
        );
        return Component.extend({});
    }
);