<?php
namespace Releva\Retargeting\Shopware\Internal;

class LocalizationHelper extends AbstractHelper {
    
    private $availibleTranslations;
    
    private $translations = array();
    
    public function getAvailibleTranslations () {
        if ($this->availibleTranslations === null) {
            $this->availibleTranslations = array();
            foreach(array_diff(scandir($this->getPlugin()->Path() . '/Localization/'), ['..', '.', ]) as $language) {
                if (file_exists($this->getPlugin()->Path() . '/Localization/'.$language.'/snippets.json')) {
                    $this->availibleTranslations[] = $language;
                }
            }
        }
        return $this->availibleTranslations;
    }
    
    private function getCurrentLocale() {
        if (isset($_SESSION['Shopware']) && isset($_SESSION['Shopware']['Auth']) && isset($_SESSION['Shopware']['Auth']->localeID)) {
            $localeId = $_SESSION['Shopware']['Auth']->locale->getId();
        } else {
            $localeId = 1;
        }
        return \Shopware()->Models()->getRepository('Shopware\Models\Shop\Locale')->find($localeId)->getLocale();
    }
    
    public function readTranslations($locale = null) {
        $locale = $locale === null ? $this->getCurrentLocale() : $locale;
        if (!array_key_exists($locale, $this->translations)) {
            $this->translations[$locale] = 
                file_exists($this->getPlugin()->Path() . '/Localization/' . $locale . '/snippets.json')
                ? (array) json_decode(trim(file_get_contents($this->getPlugin()->Path() . '/Localization/' . $locale . '/snippets.json')), true)
                : array()
            ;
        }
        return $this->translations[$locale];
    }
    
    public function translate ($key, $locale = null) {
        $translations = $this->readTranslations($locale);
        return array_key_exists($key, $translations) ? $translations[$key] : '';
    }

    public function translateRelevanzException($exception, $locale = null) {
        if ($this->translate('error_'.$exception->getCode()) !== null) {
            return $this->translate('error_'.$exception->getCode(), $locale).(
                count($exception->getSprintfArgs()) !== 0
                ? '<br />Data: '. implode(', ', $exception->getSprintfArgs())
                : ''
            );
        } else {
            return vsprintf($exception->getMessage(), $exception->getSprintfArgs());
        }
    }
}
