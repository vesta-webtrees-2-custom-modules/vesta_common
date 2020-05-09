<?php

namespace Vesta\Model;

class GovReference {

  private $id;
  private $trace;
  private $level;

  public function getId(): string {
    return $this->id;
  }
  
  public function getTrace(): Trace {
    return $this->trace;
  }
  
  public function getLevel(): int {
    return $this->level;
  }
  
  /**
   * 
   * @param string $id _GOV id
   * @param Trace $trace
   * @param int $level
   */
  public function __construct(string $id, Trace $trace, int $level = 0) {
    $this->id = $id;
    $this->trace = $trace;
    $this->level = $level;
  }

}
