/*browser:true*/
/*global define*/
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
                type: 'payuni',
                component: 'PAYUNi_Upp/js/view/payment/method-renderer/payuni'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
