/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Paysley_Paysley/js/action/set-payment-method',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, setPaymentMethodAction, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Paysley_Paysley/payment/paysley-method'
            },
            /** Redirect to Payment Form */
            placeOrderAction: function () {
                this.selectPaymentMethod(); // save selected payment method in Quote
                setPaymentMethodAction(
                    this.messageContainer,
                    {
                        method: this.getCode()
                    }
                );
                return false;
            },
            getLogos: function () {
                return window.checkoutConfig.payment.paysley.logos[this.getCode()];
            },
            initPaysley: function() {
                var billingAddress = quote.billingAddress();
                $('[data-key]').hide();
                $('[data-key='+billingAddress.countryId+']').show();
                
                $("[data-role=opc-continue]").click(function() {
                    location.assign(window.location.href.split('#')[0]+'#payment');
                    location.reload();
                });
                return false;
            }
        });
    }
);



