define([
    'Magento_Ui/js/form/element/date'
], function(Date) {
    'use strict';

    return Date.extend({
        defaults: {
            options: {
                showsDate: true,
                showsTime: true,
                timeOnly: false,
                timeFormat: 'HH:mm'
            },

            elementTmpl: 'ui/form/element/date'
        }
    });
});