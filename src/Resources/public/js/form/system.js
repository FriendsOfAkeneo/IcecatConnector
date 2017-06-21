"use strict";

define([
        'underscore',
        'oro/translator',
        'routing',
        'jquery',
        'pim/form',
        'pim/fetcher-registry',
        'pim/formatter/choices/base',
        'pim/initselect2',
        'text!pimicecatconnector/template/system/group/configuration'
    ],
    function (_,
              __,
              Routing,
              $,
              BaseForm,
              FetcherRegistry,
              ChoicesFormatter,
              initSelect2,
              template) {
        return BaseForm.extend({
            events: {
                'change .pim-icecat-config': 'updateModel',
                'click #check-connection': 'checkConnection'
            },
            isGroup: true,
            label: __('pim_icecat_connector.configuration.tab.label'),
            template: _.template(template),
            areCredentialsValid: null,
            checkConnection : function () {
                var form_username =  this.getFormData()['pim_icecat_connector___credentials_username'] ?
                    this.getFormData()['pim_icecat_connector___credentials_username'].value : '';
                var form_password = this.getFormData()['pim_icecat_connector___credentials_password'] ?
                    this.getFormData()['pim_icecat_connector___credentials_password'].value : '';
                $.ajax
                ({
                    type: "POST",
                    url: Routing.generate('pim_icecat_connector_check'),
                    data: { username: form_username, password: form_password},
                    success: function () {
                        var prototype = $('#connection-status-prototype').html();
                        var replacements = {
                            '%granted%':'granted',
                            '%icon%':'ok',
                            '%status_message%':'Credentials are valid'
                        };
                        var connectionStatusHtml = prototype.replace(/%\w+%/g, function(all) {
                            return replacements[all] || all;
                        });
                        $('#connection-status').html(connectionStatusHtml);
                    },
                    error: function(xhr, status, error) {
                        var prototype = $('#connection-status-prototype').html();
                        var replacements = {
                            '%granted%':'nonGranted',
                            '%icon%':'remove',
                            '%status_message%':'Login and/or password is not valid'
                        };
                        var connectionStatusHtml = prototype.replace(/%\w+%/g, function(all) {
                            return replacements[all] || all;
                        });
                        $('#connection-status').html(connectionStatusHtml);
                    }
                });

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    credentials_username: this.getFormData()['pim_icecat_connector___credentials_username'] ?
                        this.getFormData()['pim_icecat_connector___credentials_username'].value : '',
                    credentials_password: this.getFormData()['pim_icecat_connector___credentials_password'] ?
                        this.getFormData()['pim_icecat_connector___credentials_password'].value : '',
                    ean_attribute: this.getFormData()['pim_icecat_connector___ean_attribute'] ?
                        this.getFormData()['pim_icecat_connector___ean_attribute'].value : '',
                    description: this.getFormData()['pim_icecat_connector___description'] ?
                        this.getFormData()['pim_icecat_connector___description'].value : '',
                    short_description: this.getFormData()['pim_icecat_connector___short_description'] ?
                        this.getFormData()['pim_icecat_connector___short_description'].value : '',
                    summary_description: this.getFormData()['pim_icecat_connector___summary_description'] ?
                        this.getFormData()['pim_icecat_connector___summary_description'].value : '',
                    short_summary_description: this.getFormData()['pim_icecat_connector___short_summary_description'] ?
                        this.getFormData()['pim_icecat_connector___short_summary_description'].value : '',
                    pictures: this.getFormData()['pim_icecat_connector___pictures'] ?
                        this.getFormData()['pim_icecat_connector___pictures'].value : ''
                }));

                var searchOptions = {
                    options: {
                        types: [
                            'pim_catalog_text',
                            'pim_catalog_textarea'
                        ]
                    }
                };

                FetcherRegistry.getFetcher('attribute').search(searchOptions)
                    .then(function (attributes) {
                        var choices = _.chain(attributes)
                            .map(function (attribute) {
                                var attributeGroup = ChoicesFormatter.formatOne(attribute.group);
                                var attributeChoice = ChoicesFormatter.formatOne(attribute);
                                attributeChoice.group = attributeGroup;

                                return attributeChoice;
                            })
                            .value();
                        initSelect2.init(this.$('input.pim-icecat-config-text'), {
                            data: choices,
                            multiple: false,
                            containerCssClass: 'input-xxlarge'
                        });
                    }.bind(this));

                searchOptions = {
                    options: {
                        types: [
                            'pim_catalog_identifier',
                            'pim_catalog_text',
                            'pim_catalog_number'
                        ]
                    }
                };

                FetcherRegistry.getFetcher('attribute').search(searchOptions)
                    .then(function (attributes) {
                        var choices = _.chain(attributes)
                            .filter(function (attribute) {
                                return attribute.unique;
                            })
                            .map(function (attribute) {
                                var attributeGroup = ChoicesFormatter.formatOne(attribute.group);
                                var attributeChoice = ChoicesFormatter.formatOne(attribute);
                                attributeChoice.group = attributeGroup;

                                return attributeChoice;
                            })
                            .value();
                        initSelect2.init(this.$('#ean_attribute'), {
                            data: choices,
                            multiple: false,
                            containerCssClass: 'input-xxlarge'
                        });
                    }.bind(this));

                searchOptions = {
                    options: {
                        types: [
                            'pim_catalog_text_collection'
                        ]
                    }
                };

                FetcherRegistry.getFetcher('attribute').search(searchOptions)
                    .then(function (attributes) {
                        var choices = _.chain(attributes)
                            .map(function (attribute) {
                                var attributeGroup = ChoicesFormatter.formatOne(attribute.group);
                                var attributeChoice = ChoicesFormatter.formatOne(attribute);
                                attributeChoice.group = attributeGroup;

                                return attributeChoice;
                            })
                            .value();
                        initSelect2.init(this.$('#pictures'), {
                            data: choices,
                            multiple: false,
                            containerCssClass: 'input-xxlarge'
                        });
                    }.bind(this));

                this.$('.switch').bootstrapSwitch();

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Update model after value change
             *
             * @param {Event}
             */
            updateModel: function (event) {
                var name = 'pim_icecat_connector___' + event.target.name;
                var data = this.getFormData();
                var newValue = event.target.value;
                if (name in data) {
                    data[name].value = newValue;
                } else {
                    data[name] = {value: newValue};
                }
                this.setData(data);
            }
        });
    }
);
