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

            /**
             * Possible SDK parameters:
             * - submit_to_stack. Sends the new document to be validated in CertCapture. New certificates are automatically
             *              validated, unless this behavior is altered using the "submit_to_stack". Default is false.
             * - preview. Allows user to quickly view their new certificate so they can save or print it. Certificate is
             *              not generated in CertCapture. Default is false.
             * - customer_list. Appends a page to the generated file that includes "Customer Number", "Customer Name",
             *              and "Customer Address" of each customer associated with the certificate. Must be called at
             *              initialization of GenCert API. Default is false.
             * - upload_only. Disables the ability to complete documents by submitting information to form fields.
             *              Customers can only upload prefilled documents. Default is false.
             * - fill_only. Disables the upload of prefilled documents. Customers can only complete documents by
             *              submitting information to form fields.
             * - show_files. Displays a download link after document submission.
             * - edit_purchaser. Allows the customer to edit their information on return visits. To allow customers
             *              to edit their existing information, use edit_purchaser:true. Default is false.
             *
             *  Callback functions (https://app.certcapture.com/gencert2/js):
             *  - onUpload. Called when a document is uploaded. Access the generated certificate id with "GenCert.certificateIds".
             *  - onCancel. Used to reinitialize GenCert or provide user navigation
             *  - onManualSubmit. Used to reinitialize GenCert or navigate the user elsewhere. This occurs when nexus
             *              (exemption matrix) is set to manually collect a document. The user can upload a form when this happens.
             *  - onInit. After constructor has finished loading
             *  - beforeShow. Called before the form is shown
             *  - afterShow. Called after the form is shown
             *  - beforeValidate. Before form validation
             *  - onValidateSuccess. After form validation success, before submit
             *  - onValidateFailure. After form validation failure
             *  - onCertSuccess. After submission, when certificate is successful.  Access the generated certificate id
             *              with "GenCert.certificateIds"
             *  - onCertFailure. After submission, when certificate is failure
             *  - onCancel. User cancels the certificate generation process
             *  - onNotNeeded. Called when a Zone that does not charge sales tax is chosen
             *  - onSaveCustomer.
             *  - onSaveSignature.
             */

            this.sdkParameters = jQuery.extend({
                // Include if cert is a renewal?
                upload: true,
                submit_to_stack: Boolean(Number(this.certificatesAutoValidationDisabled)),

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
