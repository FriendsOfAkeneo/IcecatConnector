'use strict';
/**
 * Extension to add a "no settings" message
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pimicecatconnector/template/job/no_settings'
    ],
    function (_, __, BaseForm, template) {
        return BaseForm.extend({
            tagName: 'span',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                const html = this.template({
                    message: __('pim_icecat_connector.form.job_instance.no_settings')
                });

                this.$el.html(html);

                return this;
            }
        });
    }
);
