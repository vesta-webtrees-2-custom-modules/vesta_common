<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Module;

class ModuleMetaData {
  
  protected $version;
  protected $minRequiredWebtreesVersion;
  protected $minUnsupportedWebtreesVersion;
  protected $changelog;
              
  public function version(): string {
    return $this->version;
  }
  
  public function minRequiredWebtreesVersion(): string {
    return $this->minRequiredWebtreesVersion;
  }
  
  public function minUnsupportedWebtreesVersion(): string {
    return $this->minUnsupportedWebtreesVersion;
  }
    
  /**
   * 
   * @return string changelog wrt previous version, or cumulative changelog from currently installed version to target version
   */
  public function changelog(): array {
    return $this->changelog;
  }
  
  public function __construct(
          string $version,
          string $minRequiredWebtreesVersion,
          string $minUnsupportedWebtreesVersion,
          array $changelog = []) {

    $this->version = $version;
    $this->minRequiredWebtreesVersion = $minRequiredWebtreesVersion;
    $this->minUnsupportedWebtreesVersion = $minUnsupportedWebtreesVersion;
    $this->changelog = $changelog;
  }
  
  public function prependChangelogFrom(?ModuleMetaData $other): ModuleMetaData {
    if ($other === null) {
      return $this;
    }
    
    $cumulativeChangelog = array_merge($other->changelog(), $this->changelog());
    return new ModuleMetaData(
            $this->version,
            $this->minRequiredWebtreesVersion,
            $this->minUnsupportedWebtreesVersion,
            $cumulativeChangelog);
  }  
}
