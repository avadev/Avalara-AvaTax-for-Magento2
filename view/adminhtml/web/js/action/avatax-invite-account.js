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
            window.location.href = this.inviteUrl;
        }
    });
});
