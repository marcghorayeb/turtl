<?php

namespace app\extensions\data;

abstract class Parser extends \lithium\core\Object {
	protected $_autoConfig = array(
		'bank'
	);
	
	protected $_filepath;
	
	protected $_fileHandle;

	protected $_fileContents;
	
	protected $_bank = '';
	
	protected $_header = array();

	protected $_footer = array();
	
	protected $_transactions = array();
	
	protected function _init() {
		parent::_init();
		
		if (file_exists($this->_config['file']) && ($this->_fileHandle = fopen($this->_config['file'], 'r')) !== false) {
			$this->_filepath = $this->_config['file'];
		}
		
		if ($this->_config['guessBank']) {
			$this->_guessBank();
		}

		if ($this->_config['readFile']) {
			$this->_readFile();
		}
	}
	
	public function setFile($filepath) {
		if (!empty($this->_fileHandle)) {
			fclose($this->_fileHandle);
		}
		
		if (($this->_fileHandle = fopen($filepath, 'r')) !== false) {
			$this->_filepath = $filepath;
		}
	}
	
	public function parseFile($skipHeader = false) {
		$this->_parseHeader();
		$this->_parseFooter();
		$this->_parseTransactions();

		$this->_cleanInput();
	}
	
	public function getHeader() {
		return $this->_header;
	}

	public function getFooter() {
		return $this->_footer;
	}
	
	public function getTransactions() {
		return $this->_transactions;
	}
	
	public function __destruct() {
		if ($this->_fileHandle !== false) {
			fclose($this->_fileHandle);
		}
	}

	abstract protected function _readFile();
	
	abstract protected function _parseHeader();
	
	abstract protected function _parseTransactions();

	abstract protected function _cleanInput();
	
	abstract protected function _guessBank();
}
?>