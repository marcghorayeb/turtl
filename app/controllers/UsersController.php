<?php

namespace app\controllers;

use MongoDate;
use DateTime;

use app\models\Users;

use lithium\analysis\Debugger;
use lithium\security\Auth;
use lithium\util\String;

use li3_flash\extensions\storage\Flash;

class UsersController extends \app\controllers\AppBaseController {
	protected $_publicActions = array('login', 'logout', 'register', 'session', 'activate');
	
	public function login() {
		// Redirect if user is already logged in
		if (Auth::check('user')) {
			Flash::write('You are already logged in.', array('class' => 'notice'));
			return $this->redirect('accounts/summary');
		}

		if ($this->request->data && !empty($this->request->data['email'])) {
			$this->request->data['email'] = strtolower($this->request->data['email']);
			
			// Redirect whether or not the password is correct
			if (($user = Auth::check('user', $this->request))) {
				$this->_setCurrentUser(Users::create($user));

				if ($user['login']['notify']) {
					$this->_sendMail();
				}

				Users::successfulLogin($user['_id']);
				Flash::write('Connexion réussie.<br/>Dernière connexion le '.date('d M Y', $user['login']['last']), array('class' => 'success'));
				return $this->redirect('accounts/summary');
			}
			else {
				Users::failedLogin($this->request->data['email']);
				
				$this->request->data['password'] = '';
				
				Flash::write('Email and passwords do not match! Try again.', array('class' => 'error'));
			}
		}
		
		$this->set(array('loginUser' => Users::create($this->request->data)));

		$this->render(array('layout' => 'login'));
	}

	public function logout() {
		$user = Auth::check('user');
		if (!empty($user)) {
			Auth::clear('user');
			Flash::write('Successfully disconnected!', array('class' => 'success'));
		}
		else {
			Flash::write('You were not logged in. Nothing to clear.', array('class' => 'warning'));
		}

		return $this->redirect('/');
	}

	public function register () {
		$user = Auth::check('user');
		
		if (!empty($user)) {
			Flash::write('Already logged in.', array('class' => 'notice'));
			return $this->redirect('accounts/summary');
		}
		
		if (!empty($this->request->data)) {
			$this->request->data['email'] = strtolower($this->request->data['email']);
			$user = Users::findByEmail($this->request->data['email']);
			
			if (!empty($user)) {
				Flash::write('A user already exists with that e-mail!', array('class' => 'error'));
			}
			else {
				// Initialise a new user
				$user = Users::create($this->request->data);
				$success = $user->save();
				
				if ($success) {
					$registrationLink = 'https://marc.turtl.fr/users/activate/'.$user->_id.'/'.$user->registrationHash();
					$subject = 'Turtl Activation';
					$this->_setCurrentUser($user);
					$this->_sendMail(compact('registrationLink', 'subject'));

					Flash::write('Successfully registered! You can now login.', array('class' => 'success'));
					return $this->redirect('users/login');
				}
				else {
					Flash::write('Problem saving user! Alert the administrator please.', array('class' => 'error'));
				}
			}
		}
		
		if (empty($user)) {
			$user = Users::create();
		}
		else {
			$user->password = '';
			$user->passwordVerify = '';
		}
		
		$this->set(array('registeringUser' => $user));

		$this->render(array('layout' => 'login'));
	}

	public function activate($userId, $hash) {
		$user = Users::first($userId);

		if ($user->activated) {
			Flash::write('Votre compte est déjà activé', array('class' => 'notice'));
			return $this->redirect('/users/login');
		}

		if ($user->registrationHash($hash)) {
			$user->activated = true;
			$user->save();

			Flash::write('Votre compte est maintenant activé', array('class' => 'success'));
			return $this->redirect('/users/login');
		}

		Flash::write('Votre compte n\'a pas pu être activé', array('class' => 'error'));
		return $this->redirect('/users/login');
	}

	public function view() {
		$accounts = $this->_currentUser->accounts();
		$categories = $this->_currentUser->categories();

		$this->set(compact('accounts', 'categories'));
	}

	public function edit() {
		if (!empty($this->request->data)) {
			$notify = (strcmp($this->request->data['login']['notify'], '1') == 0) ? true : false;
			$this->_currentUser->login->notify = $notify;

			if (isset($this->request->data['categories'])) {
				$notifications = $this->request->data['categories'];

				foreach ($notfications as $notification) {
					if (!empty($notification['id'])) {
						$category = $this->_currentUser->category($notification['id']);
						
						unset($notification['id']);

						$category->set($notifcation);
						$category->save();
					}
				}
			}

			if ($this->_currentUser->save()) {
				Flash::write('Votre profil a été mis à jour.', array('class', 'success'));
				return $this->redirect('/users/view');
			}
		}
	}

	public function delete() {
		$categories = $this->_currentUser->categories();
		$accounts = $this->_currentUser->accounts();

		foreach ($accounts as $account) {
			$transactions = $account->transactions(0);
			
			foreach ($transactions as $transaction) {
				$transactions->delete();
			}

			$account->delete();
		}

		foreach ($categories as $category) {
			$category->delete();
		}

		$this->_currentUser->delete();

		Flash::write('Votre compte a été effacé.', array('class' => 'success'));
	}

	public function weeklySummary() {
		$subject = 'Turtl - Résumé hebdomadaire';
		$summary = $this->_currentUser->weeklySummary();
		$this->_sendMail(compact('subject', 'summary'));

		return $this->redirect('/users/view');
	}
}
?>