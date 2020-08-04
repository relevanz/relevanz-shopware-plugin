function addRelevanzRetargetingJs () {
    if (
        (
            typeof $.getCookiePreference === "undefined" //no cookie-consent-tool
            || $.getCookiePreference('relevanz') === true// cookie-consent-tool relevanz activated
        )
        && typeof relevanzRetargetingUrl !== "undefined" && $('#relevanzRetargetingJs').length === 0
    ) {
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