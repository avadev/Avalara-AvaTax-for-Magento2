define(['jquery', 'uiComponent', 'Magento_Ui/js/modal/modal', 'mage/translate'], function (jQuery, Component, modal, $t) {
    return Component.extend({
        defaults: {
            template: 'ClassyLlama_AvaTax/action/account-add-exemption',
            exemptionZone: '',
            availableExemptionZones: [],
            showSdkView: false,
            certificateUploadSuccess: false,
            sdkParameters: {}
        },

        initialize: function initialize() {
            this._super();

            this.observe(['showSdkView', 'exemptionZone', 'certificateUploadSuccess']);
            this.optionsCaption = $t('Select an Exemption Zone');
            this.onCertificateComplete = this.onCertificateComplete.bind(this);
            this.onSdkLoad = this.onSdkLoad.bind(this);

            this.sdkParameters = jQuery.extend({
                // Include if cert is a renewal?
                upload: true,

                onCertSuccess: this.onCertificateComplete,
                onManualSubmit: this.onCertificateComplete,
                onUpload: this.onCertificateComplete
            }, this.sdkParameters);

            return this;
        },

        setModalElement: function setModalElement(element) {
            this.modalElement = element;
            modal(
                {
                    'type': 'popup',
                    'modalClass': 'account-add-exemption-modal',
                    'responsive': true,
                    'innerScroll': true,
                    'buttons': []
                },
                jQuery(this.modalElement)
            );

            jQuery(this.modalElement).on('modalclosed', function () {
                this.exemptionZone('');
                this.showSdkView(false);

                if (this.certificateUploadSuccess() === true) {
                    window.location.reload();
                }
            }.bind(this));
        },

        onCertificateComplete: function onCertificateComplete() {
            this.certificateUploadSuccess(true);
        },

        onSdkLoad: function onSdkLoad(GenCert) {
            GenCert.setShipZone(this.exemptionZone());
            GenCert.show();
        },

        renderSdk: function renderSdk(element) {
            // This should be implemented through a mixin at the frontend or adminhtml area level
            throw new Error('Must be implemented');
        },

        addExemption: function addExemption() {
            jQuery(this.modalElement).modal('openModal');
        },

        closeModal: function addExemption() {
            jQuery(this.modalElement).modal('closeModal');
        },

        proceedToSdk: function proceedToSdk() {
            if (this.exemptionZone() === '' || this.exemptionZone() === void(0)) {
                return;
            }

            this.showSdkView(true);
        }
    });
});
