<?php

namespace Vesta\ControlPanelUtils\Model;

class ControlPanelSubsection {

  private $label;
  private $elements;
  private $description;

  /**
   * 
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @return ControlPanelElement[]
   */
  public function getElements() {
    return $this->elements;
  }

  /**
   * @return string|null
   */
  public function getDescription() {
    return $this->description;
  }
  
  public function __construct($label, $elements, $description = null) {
    $this->label = $label;
    $this->elements = $elements;
    $this->description = $description;
  }

}
