<?php
namespace Releva\Retargeting\Shopware\Internal;

class FormHelper extends AbstractHelper {
    
    public function setConfigForm($form) {
        $this
            ->addConfigUserId($form)
            ->addConfigApiKey($form)
            ->addConfigApiTestButton($form)
            ->addConfigRegisterButton($form)
        ;
        return $form;
    }
    
    public function getFormTranslations($form, $localizationHelper) {
        $translations = array();
        foreach($localizationHelper->getAvailibleTranslations() as $language) {
            $snippets = $localizationHelper->readTranslations($language);
            foreach ($form->getElements() as $element) {
                if (isset($snippets[$element->getName()])) {
                    $translations[$language][$element->getName()] = array(
                        'label' => $snippets[$element->getName()],
                    );
                }
            }
        }
        return $translations;
    }
    
    private function addConfigUserId($form) {
        $form->setElement('text', 'relevanzUserID', array(
            'label' => 'releva.nz User ID',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
            'stripCharsRe' => ' ',
            'description' => 'This field will set by API Key',
            'hidden' => true
        ));
        return $this;
    }
    
    private function addConfigApiKey($form) {
        $form->setElement('text', 'relevanzApiKey', array(
            'label' => 'relevan.nz API Key',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
            'stripCharsRe' => ' ',
        ));
        return $this;
    }
    
    private function addConfigApiTestButton($form) {
        $form->setElement('button', 'relevanzButtonClientTest', array(
                'label' => '<strong>Jetzt API Key testen<strong>',
                'handler' => "function(btn) {"
                . file_get_contents($this->getPlugin()->Path(). '/Views/backend/relevanz/config_form/api_test.js') . "}"
            ));
        return $this;
    }
    
    private function addConfigRegisterButton($form) {
        $form->setElement('button', 'relevanzButtonClientRegister', array(
            'label' => '<strong>Noch nicht registriert? Jetzt nachholen<strong>',
            'handler' => "function(btn) {"
            . file_get_contents($this->getPlugin()->Path() . '/Views/backend/relevanz/config_form/register_popup.js') . "}"
        ));
        return $this;
    }
    
}