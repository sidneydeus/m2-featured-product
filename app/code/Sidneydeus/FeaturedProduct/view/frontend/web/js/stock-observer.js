define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            pollInterval: 5000
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.stock = customerData.get('featured-product-stock');
            this._pollTimer = null;
            this._requestInFlight = false;

            if (!this.stock().product_id) {
                this.refreshStock();
            }

            this.startPolling();

            return this;
        },

        /**
         * Starts polling the stock section.
         */
        startPolling: function () {
            var self = this,
                interval = parseInt(this.pollInterval, 10);

            if (!interval || interval < 1000) {
                return;
            }

            this._pollTimer = setInterval(function () {
                if (document.hidden) {
                    return;
                }

                self.refreshStock();
            }, interval);
        },

        /**
         * Reloads the featured product stock section from the server.
         */
        refreshStock: function () {
            var self = this;

            if (this._requestInFlight) {
                return;
            }

            this._requestInFlight = true;

            customerData.reload(['featured-product-stock'], true)
                .always(function () {
                    self._requestInFlight = false;
                });
        },

        /**
         * Stops polling when the component is destroyed.
         *
         * @returns {Object}
         */
        destroy: function () {
            if (this._pollTimer) {
                clearInterval(this._pollTimer);
                this._pollTimer = null;
            }

            return this._super();
        }
    });
});
