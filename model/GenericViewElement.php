<?php

namespace Vesta\Model;

class GenericViewElement {

	private $main;
	private $script;
	
	/**
	 * 
	 * @return string html string
	 */
	public function getMain() {
		return $this->main;
	}

	/**
	 * 
	 * @return string html string (i.e. script tags must be included)
	 */
	public function getScript() {
		return $this->script;
	}

	public function __construct($main, $script) {
		$this->main = $main;
		$this->script = $script;
	}
	
	public static function createEmpty() {
		return new GenericViewElement('', '');
	}
	
	public static function create($main) {
		return new GenericViewElement($main, '');
	}
	
	/**
	 * 
	 * @param GenericViewElement[] $elements
	 * @return GenericViewElement
	 */
	public static function implode($elements) {
		$main = '';
		$script = '';
		foreach ($elements as $element) {
			$main .= $element->getMain();
			$script .= $element->getScript();
		}
		return new GenericViewElement($main, $script);
	}
}	