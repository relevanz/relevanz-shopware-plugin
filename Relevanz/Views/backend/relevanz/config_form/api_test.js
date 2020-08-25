var pnl = btn.up('panel');
var url = document.location.pathname + 'Relevanz/testClient';
var els = pnl.query('[isFormField]'),
        vls = {};

Ext.Array.each(els, function (el, i) {
    var v = el.getSubmitValue();
    if (v === false) {
        v = 0;
    }
    if (v === true) {
        v = 1;
    }
    vls[el.elementName] = v;
});

Ext.Ajax.request({
    url: url,
    params: vls,
    timeout: 10000,
    success: function (response, options) {
        var data = Ext.decode(response.responseText);
        if (data.ACK && data.ACK == 'Success') {
            data.ACK = '<span style=\"color: green;font-weight: bold;\">' + data.ACK + '</span>';
        }
        if (data.ACK && data.ACK != 'Success') {
            data.ACK = '<span style=\"color: red;font-weight: bold;\">' + data.ACK + '</span>';
        }
        if (data.Code && data.Code == 'OK') {
            data.Message = '<span style=\"color: green;font-weight: bold;\">' + data.Message + '</span>';
        }
        if (data.Code && data.Code != 'OK') {
            data.Message = '<span style=\"color: red;font-weight: bold;\">' + data.Message + '</span>';
        }
        var title = '<span style=\"font-weight: bold;\">' + btn.text + '</span>';
        var text = '';
        Ext.iterate(data, function (key, value) {
            if (key == 'Message' || key == 'Code') {
                text += '<strong>' + key + ':</strong> ' + value + '<br>';
            }
            if (key == 'Id') {
                Ext.Array.each(els, function (el, i) {
                    var v = el.getSubmitValue();
                    if (el.elementName == 'relevanzUserID') {
                        el.setValue(value);
                    }
                });
            }
        });
        Shopware.Notification.createStickyGrowlMessage({
            title: title,
            text: text,
            width: 440,
            log: false,
            btnDetail: {
                link: 'https://releva.nz'
            }
        });
    },
    failure: function (response, options) {
        var title = '<span style=\"font-weight: bold;\">Error</span>',
            text = '<span style=\"color: red;font-weight: bold;\">Failed! Please check the configuration options to see if the plugin is active.</span>'
        ;
        Shopware.Notification.createStickyGrowlMessage({
            title: title,
            text: text,
            width: 440,
            log: false,
            btnDetail: {
                link: 'https://releva.nz'
            }
        });
    }
});
