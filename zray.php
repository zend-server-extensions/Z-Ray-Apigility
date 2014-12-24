<?php
/*********************************
	Apigility Z-Ray Extension
	Version: 1.00
**********************************/
namespace Apigility;

use Zend\Mvc\MvcEvent;

class Apigility {
    
	private $isApigilityRoleSaved = false;
	
	public function storeTriggerExit($context, &$storage) {
	    
		$mvcEvent = $context["functionArgs"][1];
		if (class_exists('ZF\MvcAuth\MvcAuthEvent') && is_a($mvcEvent, 'ZF\MvcAuth\MvcAuthEvent') && $mvcEvent->getIdentity()) {
			//event: authentication, authentication.post authorization authorization.post in Apigility
			if (! $this->isApigilityRoleSaved &&
			      method_exists($mvcEvent, 'getIdentity') && 
			      method_exists($mvcEvent->getIdentity(), 'getRoleId')) {
			    $storage['identity_role'][] = array('roleId' => $mvcEvent->getIdentity()->getRoleId());
			    $this->isApigilityRoleSaved = true;
			}
			$storage['Mvc_Auth_Event'][] = array(	'eventName' => $context["functionArgs"][0],
    												'AuthenticationService' => get_class($mvcEvent->getAuthenticationService()) . ': Adapter-' . get_class($mvcEvent->getAuthenticationService()->getAdapter()). '  Storage-' . get_class($mvcEvent->getAuthenticationService()->getStorage()),
    												'hasAuthenticationResult' => $mvcEvent->hasAuthenticationResult(),
    												'AuthorizationService' => $mvcEvent->getAuthorizationService(),
    												'Identity' =>  $mvcEvent->getIdentity(),
    												'isAuthorized' => $mvcEvent->isAuthorized());
		}
	}
}

$apigilityStorage = new Apigility();

$apigilityExtension = new \ZRayExtension("Apigility");

$apigilityExtension->setMetadata(array(
	'logo' => base64_encode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'logo.png')),
));
$apigilityExtension->setEnabledAfter('Zend\Mvc\Application::init');
$apigilityExtension->traceFunction("Zend\EventManager\EventManager::triggerListeners",  function(){}, array($apigilityStorage, 'storeTriggerExit'));
