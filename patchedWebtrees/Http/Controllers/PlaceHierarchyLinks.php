<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Illuminate\Support\Collection;

class PlaceHierarchyLinks {
  
  protected $label;
  protected $links;
 
  public function label(): ?string {
    return $this->label;
  }
  
  /**
   * 
   * @return Collection<PlaceHierarchyLink>
   */
  public function links(): Collection {
    return $this->links;
  }
  
  public function __construct(
          ?string $label,
          Collection $links) {
    
    $this->label = $label;
    $this->links = $links;
  }
}
