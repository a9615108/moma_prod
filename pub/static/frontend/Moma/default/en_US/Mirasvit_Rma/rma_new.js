define([
    "jquery",
    'mage/backend/validation'
], function ($) {
    'use strict';

    $(document).ready(function () {
        $('[data-role=rma-submit]').click(function (e) {
            if ($(".ui-rma-items input:checked").length == 0) {
                alert($('#error_message_no_items').html());
                return false;
            }

            $('.rma-one-item').each(function (i, el) {
                if ($("input.rma-item-checkbox:checked", this).length == 0) {
                    $("input.input-text", this).val(0);
                }
            });

            return true;
        });

        $.validator.addMethod(
            'validate-rma-quantity',
            function (v, element, param) {
                if (/[^\d]/.test(v)) {
                    return false;
                }

                v = parseInt(v);
                if (isNaN(v) || v < 1 || v > param) {
                    return false;
                }
                return true;
            },
            $.mage.__('The quantity is incorrect.')
        );

        $('.rma-item-checkbox').click(function () {
            var field = $("#qty_requested" + $(this).data('item-id'));
            var reason = $("#reason_id" + $(this).data('item-id'));
            if ($(this)[0].checked) {
                field.val(field.attr('max'));
                field.addClass('required-entry validate-digits-range digits-range-1-'+field.attr('max'));
                reason.addClass('required-entry')
            } else {
                field.val(0);
                field.removeClass('required-entry validate-digits-range digits-range-1-'+field.attr('max'));
                reason.removeClass('required-entry')
            }
        });
    });
});
