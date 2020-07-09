
$.subscribe('plugin/swCookieConsentManager/onBuildCookiePreferences', function (event, plugin, preferences) {
    if ($.getCookiePreference('relevanz')) {
        alert('@todo add script to html');
    }
});