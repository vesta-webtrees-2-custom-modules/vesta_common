<?php

namespace Cissee\WebtreesExt\Http\Controllers;

class PlaceHierarchyLink {
  
  protected $label;
  protected $icon;
  protected $url;
  
  public function label(): string {
    return $this->label;
  }
  
  public function icon(): string {
    return $this->icon;
  }
  
  public function url(): ?string {
    return $this->url;
  }
  
  public function format(): string {    
    if ($this->url !== null) {
      return '<a href = "' . $this->url . '">' . $this->label . '</a>';
    }
    return $this->label;
  }
  
  public function __construct(
          string $label,
          ?string $icon,
          ?string $url) {
    
    $this->label = $label;
    $this->icon = $icon;
    $this->url = $url;
  }
}
