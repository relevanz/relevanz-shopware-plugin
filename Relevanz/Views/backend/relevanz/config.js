//{block name="backend/index/application" append}
Ext.define('Shopware.apps.Config.controller.MyMain', {
	override: 'Shopware.apps.Config.controller.Main',

	onSaveForm: function(button) {
		var me = this,
			formPanel = button.up('form'),
			basicForm = formPanel.getForm(),
			form = basicForm.getRecord(),
			values = basicForm.getFieldValues(),
			fieldName, fieldValue, valueStore;

		form.getElements().each(function(element) {
			valueStore = element.getValues();
			valueStore.removeAll();
			me.shopStore.each(function(shop) {
				fieldName = 'values[' + shop.get('id') + ']['+ element.get('id') + ']';
				fieldValue = values[fieldName];
				if(fieldValue !== null) {
					valueStore.add({
						shopId: shop.get('id'),
						value: fieldValue
					});
				}
			});
		});

		form.setDirty();

//		var title = '{s name=form/message/save_form_title}Save form{/s}',
		var title = 'Formular speichern',
			win = me.getWindow();

		form.store.add(form);
		form.store.sync({
			success :function (records, operation) {
//				var template = new Ext.Template('{s name=form/message/save_form_success}Form „[name]“ has been saved.{/s}'),
				var template = new Ext.Template('Gespeichert'),
					message = template.applyTemplate({
						name: form.data.label || form.data.name
					});
				Shopware.Notification.createGrowlMessage(title, message, win.title);

				var button = formPanel.down('button');
				if(button.elementName == 'relevanzButtonClientTest') {
					button.btnEl.dom.click();
				}
			},
			failure:function (batch) {
//				var template = new Ext.Template('{s name=form/message/save_form_error}Form „[name]“ could not be saved.{/s}'),
				var template = new Ext.Template('Daten konnten nicht gespeichert werden'),
					message = template.applyTemplate({
						name: form.data.label || form.data.name
					});
				if(batch.proxy.reader.rawData.message) {
					message += '<br />' + batch.proxy.reader.rawData.message;
				}
				Shopware.Notification.createGrowlMessage(title, message, win.title);
			}
		});
	}
});
//{/block}
