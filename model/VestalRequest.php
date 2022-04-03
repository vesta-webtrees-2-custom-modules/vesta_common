<?php

namespace Vesta\Model;

use JsonSerializable;
use function GuzzleHttp\json_encode;

class VestalRequest implements JsonSerializable {

  private $method;
  private $args;
  
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return [
      'method' => $this->method(),
      'args' => $this->args(),
    ];
  }
     
  public function debug(): string {
    return json_encode($this);
  }

  public function method(): string {
    return $this->method;
  }

  public function args(): JsonSerializable {
    return $this->args;
  }
  
  public function __construct(
          string $method, 
          JsonSerializable $args) {
    
    $this->method = $method;
    $this->args = $args;
  }
  
  public static function methodFromStd($std): string {
    return $std->method;
  }
}
