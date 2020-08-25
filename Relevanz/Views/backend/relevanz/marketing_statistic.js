//{block name="backend/relevanz/index" append}
Ext.define('Shopware.apps.Relevanz', {
	name:'Shopware.apps.Relevanz',
	extend:'Enlight.app.SubApplication',
	bulkLoad:true,
	launch: function() {

		var me = this;

		var advancedPanel = Ext.create('Ext.form.Panel', {
			//title: json.snippets.advancedStatistics,
			bodyPadding: 5,
			renderTo: Ext.body,
			height: 600,
			width: 1000,
			layout: 'anchor',
			items: [
				{
					xtype : "component",
					id    : 'iframe-win',
					autoEl : {
						tag : "iframe",
						src : '{$relevanzIFrameUrl}' + '{$relevanzApiKey}'
					}
				}
			],
			defaults: {
				anchor: '100%'
			}
		});

		var window = Ext.create('Ext.window.Window', {
			renderTo: Ext.body,
			title: 'releva.nz',
			height: 600,
			width: 1000,
			layout: 'vbox',
			align: 'stretch',
			alias: 'relevanz-window',
			items: [ advancedPanel ]
		});

		window.show();
	}
});
//{/block}
