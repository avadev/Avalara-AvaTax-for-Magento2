define([
    'jquery',
    'Magento_Sales/order/create/form',
    'Magento_Sales/order/create/scripts'
], function(jQuery) {

    AdminOrder.prototype.bindImporterOfRecordField = function(field) {
        Event.observe(field, 'change', this.changeIORField.bind(this))
    };

    AdminOrder.prototype.changeIORField = function(event) {
        var field = Event.element(event),
            data;

        data = this.serializeData(this.importerOfRecordContainer).toObject();
        this.saveData(data);
        this.loadArea('totals', true, data);
    };

});