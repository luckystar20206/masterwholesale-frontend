define([
    "jquery",
    "validation",
    "mage/mage",
    "mage/adminhtml/grid",
    "prototype"
], function (jQuery) {
    "use strict";

    var NekloCoreContact = function (config) {

        var self = this;

        self.prototype.initialize(config);

        return self;
    }

    NekloCoreContact.prototype = Class.create({
        initialize: function (config) {
            this.initConfig(config);
            this.initElements();
            this.initObservers();
        },

        initConfig: function (config) {
            this.config = config;
            this.sendUrl = this.config.sendUrl || '';

            this.successMessage = this.config.successMessage || '';
            this.errorMessage = this.config.errorMessage || '';

            this.successMessageClass = this.config.successMessageClass || '';
            this.errorMessageClass = this.config.errorMessageClass || '';


            this.formContainerId = this.config.formContainerId || '';
            this.formElementSelectorList = this.config.formElementSelectorList || [];
        },

        initElements: function () {
            this.sendButton = $(this.config.sendButtonId) || null;
            this.loadingMask = $(this.config.loadingMaskId) || null;
            this.messageContainer = $$(this.config.messageContainerSelector).first() || null;
        },

        initObservers: function () {
            if (this.sendButton) {
                this.sendButton.observe('click', this.send.bind(this));
            }
        },

        send: function () {

            if (!this.sendUrl) {
                return;
            }
            if (!this.validate()) {
                return;
            }

            var me = this;
            var sendData = {};
            $H(this.formElementSelectorList).each(function (elementSelector) {
                if (Validation.isVisible($(me.formContainerId + '_' + elementSelector.key))) {
                    sendData[elementSelector.key] = $(me.formContainerId + '_' + elementSelector.key).getValue();
                }
            });

            new Ajax.Request(
                this.sendUrl,
                {
                    method: 'post',
                    parameters: sendData,
                    onCreate: this._onSendCreate.bind(this),
                    onComplete: this._onSendComplete.bind(this),
                    onSuccess: this._onSendSuccess.bind(this),
                    onFailure: this._onSendFailure.bind(this)
                }
            );
        },

        validate: function () {
            var me = this;
            var result = true;
            $H(this.formElementSelectorList).each(function (elementSelector) {
                var element = $(me.formContainerId + '_' + elementSelector.key);
                elementSelector.value.each(function (className) {
                    element.addClassName(className);
                });
                result = Validation.validate($(me.formContainerId + '_' + elementSelector.key)) && result;
                elementSelector.value.each(function (className) {
                    element.removeClassName(className);
                });
            });
            return result;
        },

        showLoadingMask: function () {
            if (this.loadingMask) {
                this.loadingMask.show();
            }
        },

        hideLoadingMask: function () {
            if (this.loadingMask) {
                this.loadingMask.hide();
            }
        },

        _onSendCreate: function () {
            this.clearMessageContainer();
            this.showLoadingMask();
        },

        _onSendComplete: function () {
            this.hideLoadingMask();
        },

        _onSendSuccess: function (response) {
            try {
                var result = response.responseText.evalJSON();
                if (typeof(result.success) != 'undefined') {
                    if (result.success) {
                        this.showSuccess();
                        this.clearForm();
                    } else {
                        this.showError();
                    }
                }
            } catch (e) {
                this.showError();
            }
        },

        _onSendFailure: function () {
            this.showError();
        },

        showSuccess: function () {
            this.showMessage(this.successMessage, this.successMessageClass);
        },

        showError: function () {
            this.showMessage(this.errorMessage, this.errorMessageClass);
        },

        showMessage: function (message, className) {
            this.clearMessageContainer();
            var messageElement = new Element('p', {'class': className}).update(this.prepareMessage(message));
            this.messageContainer.appendChild(messageElement);
        },

        clearMessageContainer: function () {
            this.messageContainer.update('');
        },

        prepareMessage: function (message) {
            if ((typeof message) == 'string') {
                return message;
            }
            if (Array.isArray(message)) {
                return message.join("<br/>");
            }
            return '';
        },

        clearForm: function () {
            var me = this;
            $H(this.formElementSelectorList).each(function (elementSelector) {
                $(me.formContainerId + '_' + elementSelector.key).setValue('');
            });
        }
    });

    return NekloCoreContact;
});