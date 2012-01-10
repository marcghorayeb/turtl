<?php

namespace app\controllers;

use app\models\Users;
use app\models\Categories;

use lithium\security\Auth;
use lithium\template\View;

use li3_flash\extensions\storage\Flash;
use li3_swiftmailer\mailer\Message;
use li3_swiftmailer\mailer\Transports;

class AppBaseController extends \lithium\action\Controller {
	protected $_currentUser = null;
	protected $_categories = null;
	
	protected function _init() {
		parent::_init();

		$user = Auth::check('user');
		
		// Check if the url requested is a public action for a public user
		if (empty($user) && !in_array($this->request->params['action'], $this->_publicActions)) {
			Flash::write('Vous n\'avez pas le droit d\'accéder à cette page. Veuillez vous connecter à votre compte.', array('class' => 'notice'));
			return $this->redirect('users/login', array('exit' => true));
		}
		// Check if the user is logged in, create the user object and make it available to the controller.
		else if (!empty($user)) {
			$categories = Categories::parents();
			$user = Users::create($user);

			$this->_setCategories($categories);
			$this->_setCurrentUser($user);
			
			if (!$this->request->is('ajax')) { // Prepare global view vars
				$this->_render['layout'] = 'user';
				$this->set(compact('user', 'categories'));
			}
		}
	}

	protected function _setCurrentUser(&$user) {
		$this->_currentUser = $user;
	}

	protected function _setCategories(&$categories) {
		$this->_categories = $categories;
	}

	protected function _sendMail($data = array()) {
		$defaults = array(
			'from' => 'b0t@turtl.fr',
			'to' => array($this->_currentUser->email => $this->_currentUser->fullname()),
			'subject' => 'Turtl Notification'
		);

		$data += $defaults;

		$view  = new View(array(
			'loader' => 'File',
			'renderer' => 'File',
			'paths' => array(
				'template' => '{:library}/views/{:controller}/{:template}.{:type}.php'
			)
		));

		$body = $view->render(
			'template',
			compact('data'),
			array(
				'controller' => $this->request->params['controller'],
				'template'=> $this->request->params['action'],
				'type' => 'mail',
				'layout' => false
			)
		);

		if (!empty($body) || empty($data['to'])) {
			$mailer = Transports::adapter('default');
			$message = Message::newInstance()
				->setFrom($data['from'])
				->setTo($data['to'])
				->setSubject($data['subject'])
				->setBody($body);

			if ($mailer->send($message)) {
				\lithium\analysis\Logger::debug('Mail sent!');
				return true;
			}
		}

		\lithium\analysis\Logger::debug('Mail not sent!');
		return false;
	}
}
?>