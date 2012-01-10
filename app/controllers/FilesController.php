<?php

namespace app\controllers;

use app\models\Files;

use lithium\action\Response;

class FilesController extends \app\controllers\AppBaseController {
	protected $_publicActions = array();
	
	// Enable 'negotiate' so that AJAX requests are answered correctly
	protected function _init() {
		$this->_render['negotiate'] = true;
		parent::_init();
	}

	public function view() {
		if (empty($this->request->params['id'])) {
			return $this->redirect('/accounts/summary');
		}
		
		$file = $this->_currentUser->getFile($this->request->params['id']);
		$octetstream = $file->file->getBytes();

		if (empty($octetstream)) {
			return $this->redirect('/accounts/summary');
		}

		$this->set(compact('octetstream'));
		$this->_render['type'] = 'octetstream';
		$this->response->headers('download', $file->filename);
	}

	public function delete() {
		if (!empty($this->request->params['id'])) {
			$file = $this->_currentUser->getFile($this->request->params['id']);

			if (!empty($file)) {
				if (!empty($file->transaction_id)) {
					$transaction = $this->_currentUser->getTransaction($file->transaction_id);
					$files = $transaction->meta->file_id->to('array');
					$id = array_search((string) $file->_id, $files);
					unset($files[$id]);
					$transaction->meta->file_id = array_values($files); // reset indexes
					$transaction->save();
				}

				$file->delete();
			}
		}

		return $this->redirect('/accounts/summary');
	}
}