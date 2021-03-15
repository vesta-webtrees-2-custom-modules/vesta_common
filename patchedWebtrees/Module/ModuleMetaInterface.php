<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Module;

use Illuminate\Support\Collection;

interface ModuleMetaInterface {
  
  public function customModuleMetaDatasJson(): string;
   
  /**
   * 
   * @return Collection<ModuleMetaData>
   */ 
  public function customModuleMetaDatas(): Collection;
  
  public function customModuleLatestMetaDatasJsonUrl(): string;
  
  /**
   * 
   * @return Collection<ModuleMetaData>
   */
  public function customModuleLatestMetaDatas(): Collection;
  
  /**
   * 
   * @param string|null $targetWebtreesVersion if null, return metadata about currently installed version
   * @return ModuleMetaData|null
   */
  public function customModuleMetaData(?string $targetWebtreesVersion = null): ?ModuleMetaData;
}
