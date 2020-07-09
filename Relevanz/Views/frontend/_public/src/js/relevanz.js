function addRelevanzRetargetingJs () {
    if ($.getCookiePreference('relevanz') === true && typeof relevanzRetargetingUrl !== "undefined" && $('#relevanzRetargetingJs').length === 0) {
        var script = document.createElement('script');
        script.id = "relevanzRetargetingJs"
        script.type = 'text/javascript';
        script.src = relevanzRetargetingUrl;
        script.async = true;
        document.body.appendChild(script);
    }
}
addRelevanzRetargetingJs();
$.subscribe('plugin/swCookieConsentManager/onBuildCookiePreferences', addRelevanzRetargetingJs);