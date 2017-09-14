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
            fallbackChannel: null,
            supportedLocales: [
                {id: 'INT', text: "International standardized version"},
                {id: 'EN', text: "Standard or UK English"},
                {id: 'US', text: "US English"},
                {id: 'NL', text: "Dutch"},
                {id: 'FR', text: "French"},
                {id: 'DE', text: "German"},
                {id: 'IT', text: "Italian"},
                {id: 'ES', text: "Spanish"},
                {id: 'DK', text: "Danish"},
                {id: 'RU', text: "Russian"},
                {id: 'PT', text: "Portuguese"},
                {id: 'ZH', text: "Chinese (simplified)"},
                {id: 'SE', text: "Swedish"},
                {id: 'PL', text: "Polish"},
                {id: 'CZ', text: "Czech"},
                {id: 'HU', text: "Hungarian"},
                {id: 'FI', text: "Finnish"},
                {id: 'NO', text: "Norwegian"},
                {id: 'TR', text: "Turkish"},
                {id: 'BG', text: "Bulgarian"},
                {id: 'KA', text: "Georgian"},
                {id: 'RO', text: "Romanian"},
                {id: 'SR', text: "Serbian"},
                {id: 'JA', text: "Japanese"},
                {id: 'UK', text: "Ukrainian"},
                {id: 'CA', text: "Catalan"},
                {id: 'HR', text: "Croatian"},
                {id: 'AR', text: "Arabic"},
                {id: 'VI', text: "Vietnamese"},
                {id: 'HE', text: "Hebrew"},
                {id: 'ZH', text: "Chinese (traditional)"},
                {id: 'BR', text: "Brasilian Portuguese"},
                {id: 'KO', text: "Korean"},
                {id: 'EN_SG', text: "Singapore English"},
                {id: 'EN_IN', text: "Indian English"},
                {id: 'LT', text: "Lithuanian"},
                {id: 'LV', text: "Latvian"},
                {id: 'DE_CH', text: "Swiss German"},
                {id: 'ID', text: "Indonesian"},
                {id: 'SK', text: "Slovakian "}
            ],
            checkConnection: function () {
                var form_username = this.getFormData()['pim_icecat_connector___credentials_username'] ?
                    this.getFormData()['pim_icecat_connector___credentials_username'].value : '';
                var form_password = this.getFormData()['pim_icecat_connector___credentials_password'] ?
                    this.getFormData()['pim_icecat_connector___credentials_password'].value : '';
                $.ajax
                ({
                    type: "POST",
                    url: Routing.generate('pim_icecat_connector_check'),
                    data: {username: form_username, password: form_password},
                    success: function () {
                        var prototype = $('#connection-status-prototype').html();
                        var replacements = {
                            '%granted%': 'granted',
                            '%icon%': 'ok',
                            '%status_message%': 'Credentials are valid'
                        };
                        var connectionStatusHtml = prototype.replace(/%\w+%/g, function (all) {
                            return replacements[all] || all;
                        });
                        $('#connection-status').html(connectionStatusHtml);
                    },
                    error: function (xhr, status, error) {
                        var prototype = $('#connection-status-prototype').html();
                        var replacements = {
                            '%granted%': 'nonGranted',
                            '%icon%': 'remove',
                            '%status_message%': 'Login and/or password is not valid'
                        };
                        var connectionStatusHtml = prototype.replace(/%\w+%/g, function (all) {
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
                    locales: this.getFormData()['pim_icecat_connector___locales'] ?
                        this.getFormData()['pim_icecat_connector___locales'].value : '',
                    fallback_locale: this.getFormData()['pim_icecat_connector___fallback_locale'] ?
                        this.getFormData()['pim_icecat_connector___fallback_locale'].value : '',
                    scope:  this.getFormData()['pim_icecat_connector___scope'] ?
                        this.getFormData()['pim_icecat_connector___scope'].value : '',
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



                FetcherRegistry.getFetcher('channel').search()
                    .then(function (channel) {
                        var choices = _.chain(channel)
                            .map(function (channel) {
                                return ChoicesFormatter.formatOne(channel);
                            })
                            .value();
                        initSelect2.init(this.$('#scope'), {
                            data: choices,
                            multiple: false,
                            containerCssClass: 'input-xxlarge'
                        });
                    }.bind(this));

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

                initSelect2.init(this.$('#locales'), {
                    data: this.supportedLocales,
                    multiple: true,
                    containerCssClass: 'input-xxlarge'
                });

                initSelect2.init(this.$('#fallback_locale'), {
                    data: this.supportedLocales,
                    multiple: false,
                    containerCssClass: 'input-xxlarge'
                });

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
