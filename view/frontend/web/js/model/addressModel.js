define(
    ['ko'],
    function (ko) {
        'use strict';
        var originalAddress = ko.observable(null);
        var validAddress = ko.observable(null);
        return {
            originalAddress: originalAddress,
            validAddress: validAddress
        };
    }
);
