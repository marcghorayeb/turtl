<?php

namespace app\extensions\data\parser;

use MongoDate;
use DateTime;

class CSV extends \app\extensions\data\Parser {
	protected $_headerTemplates = array(
		'SG' => array(
			0 => 'id:/^[=]["](.*)["]$/|period.start|period.end|period.activityCount|balance.date|balance.amount',
			1 => '',
			2 => ''
		),
		'HSBC' => array(
			0 => ':/CARTE N° (?<id>.*) du (?<period_start>.*) au (?<period_end>.*)$/u||||',
			1 => ':/ . (?<defaultCurrency>.)$/u',
			2 => '',
			3 => ''
		),
		'CE' => array(
			0 => '',
			1 => ':/ . (?<id>.*)$/u|:/ . (?<title>.*)$/u|:/ . (?<defaultCurrency>.)$/u',
			2 => '',
			3 => ''
		)
	);

	protected $_footerTemplates = array(
		'SG' => array(
		),
		'HSBC' => array(
			0 => '|balance.date|balance.amount||'
		),
		'CE' => array(
		)
	);
	
	protected $_lineTemplates = array(
		'SG' => 'date|title|description|amount|currency',
		'HSBC' => 'date|meta.date|description||amount',
		'CE' => 'date|meta.id:/(.*) [-]$/|title|debit|credit|description'
	);

	protected function _readFile() {
		$this->_fileContents = array();

		while (($line = fgets($this->_fileHandle)) !== false) {
			if (strcmp(mb_detect_encoding($line.'a','UTF-8, ISO-8859-1'), 'UTF-8') !== 0) {
				$line = utf8_encode($line);
			}

			$line = str_getcsv($line, ';');
			
			array_walk($line, function (&$item, $key) {
				$item = trim($item);
			});

			$this->_fileContents[] = $line;
		}
	}
	
	protected function _parseHeader() {
		$default = array(
			'defaultCurrency' => 'EUR'
		);

		$templates = $this->_headerTemplates[$this->_bank];
		
		foreach ($templates as $lineNumber => $lineTmpl) {
			$line = $this->_fileContents[$lineNumber];
			$lineTmpl = explode('|', $lineTmpl);

			if (!empty($lineTmpl) && !empty($line)) {
				$data = $this->_extractFromTemplate($line, $lineTmpl);

				if (!empty($data)) {
					$this->_header += $data;
				}
			}
		}

		$this->_header += $default;
	}

	protected function _parseFooter() {
		$templates = $this->_footerTemplates[$this->_bank];
		$lastIndex = count($this->_fileContents) - 1;
		
		foreach ($templates as $lineNumber => $lineTmpl) {
			$line = $this->_fileContents[$lastIndex - $lineNumber];
			$lineTmpl = explode('|', $lineTmpl);

			if (!empty($lineTmpl) && !empty($line)) {
				$data = $this->_extractFromTemplate($line, $lineTmpl);

				if (!empty($data)) {
					$this->_footer += $data;
				}	
			}
		}
	}

	protected function _parseTransactions() {
		$default = array(
			'currency' => $this->_header['defaultCurrency'],
			'credit' => 0,
			'debit' => 0,
			'title' => '',
			'description' => ''
		);

		$lineTemplate = explode('|', $this->_lineTemplates[$this->_bank]);
		$firstTransactionIndex = count($this->_headerTemplates[$this->_bank]);
		$lastTransactionIndex = count($this->_fileContents) - count($this->_footerTemplates[$this->_bank]);

		for ($i = $firstTransactionIndex; $i < $lastTransactionIndex; $i++) {
			$line = $this->_fileContents[$i];

			if (!empty($line) && !empty($lineTemplate)) {
				$data = $this->_extractFromTemplate($line, $lineTemplate);

				if (!empty($data)) {
					if (isset($data['amount'])) {
						$data['credit'] = ($data['amount'] > 0) ? $data['amount'] : 0;
						$data['debit'] = ($data['amount'] < 0) ? $data['amount'] : 0;
						unset($data['amount']);
					}

					$this->_transactions[] = $data + $default;
				}
			}
		}
	}

	protected function _extractFromTemplate($line, $lineTemplate) {
		$fields = array();

		foreach ($lineTemplate as $column => $field) {
			if (!empty($field)) {
				$data = array();

				if (strpos($field, ':') !== false) {
					$f = str_replace(':', '', strstr($field, ':', true));
					$regexp = str_replace(':', '', strstr($field, ':', false));

					if (!empty($f)) {
						preg_match($regexp, $line[$column], $matches);

						$value = empty($matches[1]) ? $line[$column] : $matches[1];
						$data = array($f => $value);
					}
					else if (!empty($regexp)) {
						preg_match($regexp, $line[$column], $matches);

						foreach ($matches as $field => $value) {
							if (!is_int($field)) {
								$field = str_replace('_', '.', $field);
								$data += array($field => $value);
							}
						}
					}
				}
				else {
					$data = array($field => $line[$column]);
				}

				if (!empty($data)) {
					$fields += $data;
				}
			}
		}

		return $fields;
	}

	protected function _cleanInput() {
		array_walk_recursive(
			$this->_header,
			function (&$value, $field) {
				switch ($field) {
					case "period.start":
					case "period.end":
					case "balance.date":
						$date = DateTime::createFromFormat('d/m/Y', $value);
						$value = $date->getTimeStamp();
						break;
					case "period.activityCount":
						$value = intval($value);
						break;
					case "balance.amount":
						$value = floatval(str_replace(',', '.', $value));
						break;
				}
			}
		);

		array_walk_recursive(
			$this->_footer,
			function (&$value, $field) {
				switch ($field) {
					case "period.start":
					case "period.end":
					case "balance.date":
						$date = DateTime::createFromFormat('d/m/Y', $value);
						$value = $date->getTimeStamp();
						break;
					case "period.activityCount":
						$value = intval($value);
						break;
					case "balance.amount":
						$value = floatval(str_replace(',', '.', $value));
						break;
				}
			}
		);
		
		array_walk_recursive(
			$this->_transactions,
			function (&$value, $field) {
				switch ($field) {
					case 'title':
					case 'description':
						$value = trim($value);
						break;
					case 'credit':
					case 'debit':
						$value = floatval(str_replace(',', '.', $value));
						break;
					case 'date':
						$date = DateTime::createFromFormat('d/m/Y', $value);
						$value = $date->getTimeStamp();
						break;
				}
			}
		);
	}
	
	// @todo
	protected function _guessBank() {
		$bestGuess = array(
			'errors' => 999,
			'bankName' => ''
		);
		
		foreach ($this->_headerTemplates as $bank => $template) {
			$id = '';
			$error = 0;
			
			foreach ($template as $lineNumber => $lineTpl) {
				$line = fgetcsv($this->_fileHandle, 0, ';');
				
				if (empty($lineTpl) && !empty($line)) {
					$error++;
				}
				else {
					if (!empty($lineTpl) && !empty($line)) {
						$cols = explode('|', $lineTpl);
						if (count($cols) != count($line)) {
							$error++;
						}
						
						foreach ($cols as $col => $field) {
							if (!empty($field)) {
								switch ($field) {
									case "id":
										preg_match('/^[=]["](.*)["]$/', $line[$col], $matches);
										if (!empty($matches[1])) {
											$id = $matches[1];
										}
										else {
											$id = $line[$col];
										}
										break;
									default:
										break;
								}
							}
						}	
					}
				}
			}
			
			if (empty($id)) {
				$error++;
			}
			
			if ($error < $bestGuess['errors']) {
				$bestGuess['errors'] = $error;
				$bestGuess['bankName'] = $bank;
			}
		}
		
		$this->_bank = $bestGuess['bankName'];
		$this->header['bankName'] = $this->_bank;
	}
}
?>