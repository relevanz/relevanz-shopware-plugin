<?php
class Shopware_Plugins_Backend_Relevanz_Bootstrap extends Shopware_Components_Plugin_Bootstrap {
	/**
	* Returns the human readable data of the plugin
	*
	* @return string
	*/
	protected $name = 'releva.nz';

	protected $languages = array(
		'de_DE',
		'en_GB',
	);

	protected $formTranslations = array(
		'relevanzUserID',
		'relevanzApiKey',
		'relevanzButtonClientTest',
		'relevanzButtonClientRegister',
	);

	protected $info = array(
		'version'		=> '1.1.0',
		'label'			=> 'releva.nz retargeting',
		'description'	=> 'releva.nz retargeting',
		'supplier'		=> 'releva.nz',
		'author'			=> 'releva.nz',
		'support'		=> 'releva.nz',
		'copyright'		=> 'releva.nz',
		'link'			=> 'http://www.releva.nz',
		'source'		=> null,
		'changes'		=> null,
		'license'		=> null,
		'revision'		=> null
	);

	public $apiUrl = 'https://api.hyj.mobi/';

	/*
	* -------------------------
	* General setup
	* -------------------------
	*/
	public function install() {
		$this->createRTEvents();

		$this->createEvents();
		$this->createMenu();
		$this->createForm();
		$this->createTranslations();

		return true;
	}

	public function uninstall() {
		return true;
	}

	public function update($oldVersion) {
		return true;
	}

	public function enable() {
		return array(
			'success' => true,
			'invalidateCache' => array('config', 'backend', 'proxy')
		);
	}

	public function disable() {
		return array(
			'success' => true,
			'invalidateCache' => array('config', 'backend')
		);
	}

	public function getInfo() {
		$snippets = $this->getSnippets();

		return array(
			'version' => $this->info['version'],
			'author' => $this->info['author'],
			'label' => $this->info['label'],
			'description' => '<p style="font-size:12px; font-weight: bold;">releva.nz retargeting<br /><a href="http://www.releva.nz" target="_blank">'.$snippets['notRegistered'].'</a></p>',
			'copyright' => 'Copyright © 2016-2018, '.$this->info['copyright'],
			'support' => 'support@releva.nz',
			'link' => $this->info['link'],
		);
	}

	public function getVersion() {
				return $this->info['version'];
	}

	public function getData() {
		$configData = $this->Config()->toArray();

		return $configData;
	}

	/*
	* ---------------------------------
	* RT CODE
	* ---------------------------------
	*/

	protected function createRTEvents() {
		$this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend', 'onRTPostDispatch');

		return true;
	}

	public function onRTPostDispatch(Enlight_Controller_ActionEventArgs $args) {
		$subject = $args->getSubject();
		$view = $subject->View();
		$request  = $subject->Request();
		$action = $request->getActionName();

		
		$version = Shopware()->Shop()->getTemplate()->getVersion();
		$view->addTemplateDir(__DIR__ . '/Views/');

		if ($request->isXmlHttpRequest()) {
			return;
		}

		$configData = $this->Config()->toArray();
		$view->baseURLRT = 'https://pix.hyj.mobi/rt?t=d&';
		$view->baseURLConv = 'https://d.hyj.mobi/convNetw?';
		$view->CampaignID = $configData['relevanzUserID'];
	}

	/*
	* ---------------------------------
	* Statistics setup
	* ---------------------------------
	*/
	private function createForm() {
		$snippets = $this->getSnippets();
		$form = $this->Form();

		$form->setElement(
			'text',
			'relevanzUserID',
			array(
				'label' => 'releva.nz User ID',
				'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
				'stripCharsRe' => ' ',
				'description' => 'This field will set by API Key',
				'hidden' => true
			)
		);

		$form->setElement(
			'text',
			'relevanzApiKey',
			array(
				'label' => 'relevan.nz API Key',
				'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
				'stripCharsRe' => ' ',
			)
		);

		if(is_file(__DIR__ . '/Views/backend/relevanz/test.js')) {
			$form->setElement(
				'button',
				'relevanzButtonClientTest',
				array(
					'label' => '<strong>Jetzt API Key testen<strong>',
					'handler' => "function(btn) {"
						. file_get_contents(__DIR__ . '/Views/backend/relevanz/test.js') . "}"
				)
			);
		}

        if(is_file(__DIR__ . '/Views/backend/relevanz/register.js')) {
            $form->setElement(
                'button',
                'relevanzButtonClientRegister',
                array(
                    'label' => '<strong>Noch nicht registriert? Jetzt nachholen<strong>',
                    'handler' => "function(btn) {"
                        . file_get_contents(__DIR__ . '/Views/backend/relevanz/register.js') . "}"
                )
            );
        }

		$translations = array();
		foreach($this->languages as $language) {
			$snippets = $this->readTranslations($language);
			foreach($this->formTranslations as $formTranslationKey) {
				if(isset($snippets[$formTranslationKey])) {
					$translations[$language][$formTranslationKey] = array(
						'label' => $snippets[$formTranslationKey],
					);
				}
			}
		}
		$this->addFormTranslations($translations);
	}

	protected function createEvents() {
		$this->subscribeEvent(
			'Enlight_Controller_Action_PostDispatch_Backend_Index',
			'PostDispatchBackendPluginManager'
		);

		$this->subscribeEvent(
			'Enlight_Controller_Dispatcher_ControllerPath_Backend_Relevanz',
			'getBackendController'
		);

		$this->subscribeEvent(
			'Enlight_Controller_Action_PostDispatch_Backend_Relevanz',
			'onBackendWaveCdn'
		);

		$this->subscribeEvent(
			'Shopware_Controllers_Backend_Config_After_Save_Config_Element',
			'onConfigElementSave'
		);

		$this->registerController('Backend', 'Relevanz');

		return true;
	}

	public function onBackendWaveCdn(Enlight_Event_EventArgs $args) {
		$controller = $args->getSubject();
		$view = $controller->View();
		$request = $controller->Request();

		$snippets = $this->getSnippets();

		$view->waveCdnSnippets = json_encode($snippets);

		$configData = $this->Config()->toArray();

		$view->addTemplateDir($this->Path().'Views/');
		$view->extendsTemplate('backend/relevanz/relevanz_app.js');

		$relevanzApiKeyValue = $configData['relevanzApiKey'];

		$waveConfigJson = array(
			'relevanzApiKey'			=> $relevanzApiKeyValue
		);
		$view->waveConfigJson = json_encode($waveConfigJson);
	}

	public function readTranslations($locale) {
		if(file_exists(__DIR__ . '/Localization/'.$locale.'/snippets.json')) {
			$snippets = (array)json_decode(trim(file_get_contents(__DIR__ . '/Localization/'.$locale.'/snippets.json')));
		} else {
			$snippets = array();
		}

		return $snippets;
	}

	public function getSnippets() {
		if(isset($_SESSION['Shopware']) && isset($_SESSION['Shopware']['Auth']) && isset($_SESSION['Shopware']['Auth']->localeID)) {
			$locale = $_SESSION['Shopware']['Auth']->locale;
			$localeId = $locale->getId();
		} else {
			$localeId = 1;
		}

		$locale = Shopware()->Models()->getRepository('Shopware\Models\Shop\Locale');
		$localeModel = $locale->find($localeId);
		$currentLocale = $localeModel->getLocale();

		$snippets = $this->readTranslations($currentLocale);

		return $snippets;
	}

	public function PostDispatchBackendPluginManager(Enlight_Event_EventArgs $args) {
		$controller = $args->getSubject();
		$view = $controller->View();

		$view->addTemplateDir($this->Path().'Views/');
		$view->extendsTemplate('backend/relevanz/form.js');
		$view->extendsTemplate('backend/relevanz/config.js');
	}

	public function saveFormActionBefore(Enlight_Event_EventArgs $args) {
		$subject = $args->getSubject();
		$view = $subject->View();
	}

	public function getBackendController(Enlight_Event_EventArgs $args) {
		$this->Application()->Template()->addTemplateDir(
			$this->Path().'Views/'
		);

		$this->registerCustomModels();

		return $this->Path().'/Controllers/Backend/Relevanz.php';
	}

	public function onConfigElementSave($params) {
		$element = $params['element'];
		$subject = $params['subject'];
    $config = $subject->Request()->getParams();

		if($element->getName() == 'relevanzApiKey') {
			$value = $config['relevanzApiKey'];
			$elements = $config['elements'];
			for($i = 0; $i < count($elements); $i++) {
				if($elements[$i]['name'] == 'relevanzApiKey') {
					$values = $elements[$i]['values'];
					for($j = 0; $j < count($values); $j++) {
						if($values[$j]['shopId'] == \Shopware\Models\Config\Element::SCOPE_SHOP) {
							$value = $values[$j]['value'];
							$return = $this->getUserIdAction($value);
						}
					}
				}
			}
		}
	}

	public function getUserData($apiKey, $apiBudget) {

		$snippets = $this->getSnippets();

		if($apiKey) {
			try {
				$testLink = $this->apiUrl.'user/get?apikey='.urlencode(trim($apiKey));
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $testLink);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$userData = curl_exec($ch);
				$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);

				if($status != 200) {
					try {
						$userData = json_decode($userData);
						$message = $snippets['serverResponse'].': '.$userData->message;
					} catch (Exception $e) {
						$message = $snippets['serverReturnCode'].' '.$status;
					}
					$userId = '';
					$code = $snippets['error'];
					$userData = array();
				} else {
					$userData = json_decode($userData);
					$message = $snippets['succesfullySaveData'];
					$userId = $userData->user_id;
					$code = $snippets['ok'];
				}

				$data = array(
					'Code'		=> $code,
					'Message'	=> $message,
					'Id'		=> $userId,
					'UserData'	=> $userData,
				);
			} catch (Exception $e) {
				$data = array(
					'Code'		=> $e->getCode(),
					'Message'	=> $e->getMessage(),
				);
			}
		} else {
			$data = array(
				'Code'		=> $snippets['error'],
				'Message'	=> $snippets['messageApiKeyCanNotBeEmpty'],
				'Id'		=> '',
			);
		}

		return array(
			'userId' => $userId,
			'data' => $data,
		);
	}

	public function getUserIdAction($apiKey) {

		$snippets = $this->getSnippets();

		$readData = $this->getUserData($apiKey);

		$userId = $readData['userId'];
		$message = $readData['data']['Message'];

		if($readData['userId']) {
			if($userId) {
				$form = $this->Form();

				$relevanzApiKeyId = $form->getElement('relevanzApiKey')->getId();
				$relevanzApiKeyValue = $apiKey;
				$relevanzUserIDId = $form->getElement('relevanzUserID')->getId();
				$relevanzUserIDValue = $userId;

				$sql = "SELECT * FROM `s_core_config_values` WHERE element_id = ? AND shop_id = ?";
				$result = Shopware()->Db()->fetchRow($sql, array($relevanzApiKeyId, \Shopware\Models\Config\Element::SCOPE_SHOP));
				if(isset($result['id'])) {
					$sql = "UPDATE `s_core_config_values` SET `value`= ? WHERE id = ?";
					Shopware()->Db()->query($sql, array(serialize($relevanzApiKeyValue), $result['id']));
				} else {
					$sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES (?, ?, ?)";
					Shopware()->Db()->query($sql, array($relevanzApiKeyId, \Shopware\Models\Config\Element::SCOPE_SHOP, serialize($relevanzApiKeyValue)));
				}

				$sql = "SELECT * FROM `s_core_config_values` WHERE element_id = ? AND shop_id = ?";
				$result = Shopware()->Db()->fetchRow($sql, array($relevanzUserIDId, \Shopware\Models\Config\Element::SCOPE_SHOP));
				if(isset($result['id'])) {
					$sql = "UPDATE `s_core_config_values` SET `value`= ? WHERE id = ?";
					Shopware()->Db()->query($sql, array(serialize($relevanzUserIDValue), $result['id']));
				} else {
					$sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES (?, ?, ?)";
					Shopware()->Db()->query($sql, array($relevanzUserIDId, \Shopware\Models\Config\Element::SCOPE_SHOP, serialize($relevanzUserIDValue)));
				}

				$data = array(
					'Code'		=> $snippets['ok'],
					'Message'	=> $message,
					'Id'		=> $userId,
				);
			} else {
				$data = array(
					'Code'		=> $snippets['error'],
					'Message'	=> $message,
				);
			}
		} else {
			$data = array(
				'Code'		=> $snippets['error'],
				'Message'	=> $message,
			);
		}

		return $data;
	}

	public function indexAction() {
	}

	private function createMenu() {
		$parent = $this->Menu()->findOneBy(array('label' => 'Marketing'));
		$this->createMenuItem(
			array(
				'label' => 'releva.nz',
				'controller' => 'Relevanz',
				'action' => 'Index',
				'class' => 'sprite-globe',
				'active' => 1,
				'parent' => $parent,
			)
		);
	}

	private function createTranslations() {
		$form = $this->Form();
	}
}
