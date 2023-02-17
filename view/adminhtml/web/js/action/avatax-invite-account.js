define(['jquery', 'uiComponent', 'Magento_Ui/js/modal/modal', 'mage/translate'], function (jQuery, Component, modal, $t) {
    return Component.extend({
        defaults: {
            template: 'ClassyLlama_AvaTax/action/avatax-invite-account',
            customerId: null,
            hasDefaultBillingAddress: false,
            inviteUrl: null
        },

        inviteCustomer: function addExemption() {
            jQuery(this.modalElement).modal('openModal');
        },

        setModalElement: function setModalElement(element) {
            this.modalElement = element;
            modal(
                {
                    'type': 'popup',
                    'modalClass': 'avatax-invite-account-modal',
                    'responsive': true,
                    'innerScroll': true,
                    'buttons': []
                },
                jQuery(this.modalElement)
            );
        },

        closeModal: function addExemption() {
            jQuery(this.modalElement).modal('closeModal');
        },

        sendInvite: function sendInvite() {
            if (confirm('This customer will be synced to AvaTax (using the customer\'s email and default billing address) and AvaTax will send an email to the customer, asking them to add an exemption certificate in the AvaTax interface. Would you like to proceed?')) {
                window.location.href = this.inviteUrl;
            }
        }
    });
});
