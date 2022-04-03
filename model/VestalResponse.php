<?php

namespace Vesta\Model;

use JsonSerializable;
use function GuzzleHttp\json_encode;

class VestalResponse implements JsonSerializable {

  private $classAttr;
  private $html;
  
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return [
      'classAttr' => $this->classAttr(),
      'html' => $this->html(),
    ];
  }
     
  public function debug(): string {
    return json_encode($this);
  }

  public function classAttr(): string {
    return $this->classAttr;
  }
  
  /**
   * 
   * @return string html including tag to replace all tags with classAttr
   */
  public function html(): string {
    return $this->html;
  }
  
  public function __construct(
          string $classAttr, 
          string $html) {
    
    $this->classAttr = $classAttr;
    $this->html = $html;
  }
}
