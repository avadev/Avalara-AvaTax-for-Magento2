define(['jquery', 'uiComponent', 'Magento_Ui/js/modal/modal', 'certificatesSdk'], function (jQuery, Component, modal, sdk) {
    return Component.extend({
        defaults: {
            template: 'ClassyLlama_AvaTax/action/account-add-exemption',
            exemptionZone: '',
            availableExemptionZones: ['Missouri'],
            showSdkView: false,
            certificateUploadSuccess: false
        },

        initialize: function initialize() {
            this._super();

            this.observe(['showSdkView', 'exemptionZone', 'certificateUploadSuccess']);

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

        renderSdk: function renderSdk(element) {
            var onCertificateComplete = this.onCertificateComplete.bind(this);
            sdk(element, {
                // Include if cert is a renewal?
                upload: true,

                onCertSuccess: onCertificateComplete,
                onManualSubmit: onCertificateComplete,
                onUpload: onCertificateComplete
            }).then(function () {
                GenCert.setShipZone(this.exemptionZone());
                GenCert.show();
            }.bind(this))
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
