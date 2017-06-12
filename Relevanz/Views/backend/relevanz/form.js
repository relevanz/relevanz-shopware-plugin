//{block name="backend/index/application" append}
Ext.define('Shopware.apps.PluginManager.controller.MyPlugin', {
	override: 'Shopware.apps.PluginManager.controller.Plugin',

	saveConfiguration: function(plugin, form) {
		var me = this;

		form.onSaveForm(form, false, function() {
			var win = form.up('window');
			var button = form.down('button');
			if(button.elementName == 'relevanzButtonClientTest') {
				button.btnEl.dom.click();
			}
		});
	}
});
//{/block}
