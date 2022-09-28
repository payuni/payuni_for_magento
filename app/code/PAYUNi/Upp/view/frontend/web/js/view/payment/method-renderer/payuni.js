/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function ($, Component, url, redirectOnSuccessAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'PAYUNi_Upp/payment/payunipayment',
            },
            getCode: function() {
                return 'payuni';
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                // Override this function and put after place order logic here
                redirectOnSuccessAction.redirectUrl = url.build('payuni/payment/redirect');
                this.redirectAfterPlaceOrder = true;
            },
        });
    }
);