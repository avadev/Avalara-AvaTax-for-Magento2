/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2018 Classy Llama Studios, LLC
 */
define(
    [
        'jquery'
    ],
    function(
        $
    ) {
        'use strict';

        return {
            /**
             * Get list of regions
             * @returns {Object}
             */
            regions: null,
            setRegions: function(url) {
                this.regions = $.ajax({
                    showLoader: false,
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    success: function (response) {
                        return response;
                    }
                });
            }
        };
    }
);
