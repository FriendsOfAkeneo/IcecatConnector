'use strict';
/**
 * Icecat enrichment mass edit operation.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/mass-edit-form/product/operation',
        'pimicecatconnector/template/mass_edit/enrich',
        'pim/user-context'
    ],
    function (_, BaseOperation, template, UserContext) {
        return BaseOperation.extend({
            template: _.template(template),

            render: function () {
                this.$el.html(this.template({
                    value: this.getValue(),
                    readOnly: this.readOnly
                }));
                return this;
            },

            updateModel: function (event) {
                this.setValue(event.target.value);
            },

            setValue: function (comment) {
                let data = this.getFormData();
                data.actions = [{
                    field: 'comment',
                    value: comment,
                    username: UserContext.get('username')
                }];
                this.setData(data);
            },

            getValue: function () {
                const action = _.findWhere(this.getFormData().actions, {field: 'comment'});
                return action ? action.value : null;
            }
        });
    }
);
