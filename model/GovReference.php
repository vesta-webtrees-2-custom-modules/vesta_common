<?php

namespace Vesta\Model;

class GovReference {

  private $id;
  private $trace;

  public function getId(): string {
    return $this->id;
  }
  
  public function getTrace(): Trace {
    return $this->trace;
  }
  
  /**
   * 
   * @param string $id _GOV id
   * @param string $trace
   */
  public function __construct(string $id, Trace $trace) {
    $this->id = $id;
    $this->trace = $trace;
  }

}
