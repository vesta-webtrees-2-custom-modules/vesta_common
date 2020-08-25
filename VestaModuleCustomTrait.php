<?php

namespace Vesta;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\Cache;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function app;

trait VestaModuleCustomTrait {
  
  /**
   * Additional/updated translations.
   *
   * @param string $language
   *
   * @return string[]
   */
  public function customTranslations(string $language): array {
    $languageFile1 = $this->resourcesFolder() . 'lang/' . $language . '.mo';
    $languageFile2 = $this->resourcesFolder() . 'lang/' . $language . '.csv';
    $ret = [];
    if (file_exists($languageFile1)) {
      $ret = (new Translation($languageFile1))->asArray();
    }
    if (file_exists($languageFile2)) {
      //we may have both!
      $ret = array_merge($ret, (new Translation($languageFile2))->asArray());
    }
    return $ret;
  }
  
  //taken from ModuleCustomTrait
  public function customModuleLatestVersion(): string {
    // No update URL provided.
    if ($this->customModuleLatestVersionUrl() === '') {
        return $this->customModuleVersion();
    }
    
    $cache = app('cache.files');
    assert($cache instanceof Cache);

    return $cache->remember($this->name() . '-latest-version', function () {
        try {
            $client = new Client([
                'timeout' => 3,
            ]);

            $response = $client->get($this->customModuleLatestVersionUrl());

            if ($response->getStatusCode() === StatusCodeInterface::STATUS_OK) {
                $version = $response->getBody()->getContents();

                //[RC] adjusted: don't check - why force a specific versioning scheme
                // Does the response look like a version?
                //if (preg_match('/^\d+\.\d+\.\d+/', $version)) {
                    return $version;
                //}
            }
            
        } catch (RequestException $ex) {
            // Can't connect to the server?
        }

        return $this->customModuleVersion();
    }, 3600); //ModuleCustomTrait has 1 day, we use 1 hour
  }
  
  protected function flashWhatsNew($namespace, $target_version): bool {
    $pref = 'WHATS_NEW';
    
    $current_version = intval($this->getPreference($pref, '0'));
    $updates_applied = false;
    
    //flash the messages, one version at a time.
    while ($current_version < $target_version) {
      $class = $namespace . '\\WhatsNew' . $current_version;
      
      if (class_exists($class)) {
        $whatsNew = new $class();
        FlashMessages::addMessage(I18N::translate("What's new? ") . $whatsNew->getMessage());        
      } //else removed, apparently
      $current_version++;

      $this->setPreference($pref, (string)$current_version);
      $updates_applied = true;
    }

    return $updates_applied;
  }
}
