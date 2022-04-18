<?php

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Webtrees;

$loader = new ClassLoader();
$loader->addPsr4('Vesta\\', __DIR__);
$loader->addPsr4('Vesta\\Hook\\HookInterfaces\\', __DIR__ . "/HookInterfaces");
$loader->addPsr4('Vesta\\Model\\', __DIR__ . "/model");
$loader->addPsr4('Vesta\\ControlPanelUtils\\', __DIR__ . "/controlpanel");
$loader->addPsr4('Vesta\\ControlPanelUtils\\Model\\', __DIR__ . "/controlpanel/model");
$loader->addPsr4('Vesta\\ControlPanelUtils\\Model\\', __DIR__ . "/controlpanel/model/elements");

$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");
$loader->addPsr4('Cissee\\WebtreesExt\\WhatsNew\\', __DIR__ . "/patchedWebtrees/WhatsNew");
$loader->addPsr4('Cissee\\WebtreesExt\\Functions\\', __DIR__ . "/patchedWebtrees/functions");
$loader->addPsr4('Cissee\\WebtreesExt\\Contracts\\', __DIR__ . "/patchedWebtrees/Contracts");
$loader->addPsr4('Cissee\\WebtreesExt\\Module\\', __DIR__ . "/patchedWebtrees/Module");
$loader->addPsr4('Cissee\\WebtreesExt\\Elements\\', __DIR__ . "/patchedWebtrees/Elements");
$loader->addPsr4('Cissee\\WebtreesExt\\Http\\Controllers\\', __DIR__ . "/patchedWebtrees/Http/Controllers");
$loader->addPsr4('Cissee\\WebtreesExt\\Http\\RequestHandlers\\', __DIR__ . "/patchedWebtrees/Http/RequestHandlers");

$loader->register();

$classMap = array();

if (str_starts_with(Webtrees::VERSION, '2.1')) {
    //no-op (GedcomTag is obsolete)
} else {
    //GedcomTag:
    //adjustments for shared places:
    //adjustment for _LOC:_LOC etc hopefully possible in webtrees 2.1.x
    //
    //other adjustments
    //_FSFTID
    $extend = !class_exists("Fisharebest\Webtrees\GedcomTag", false);
    if ($extend) {
      require_once __DIR__ . '/replacedWebtrees/app/GedcomTag.php';
    }
    
    //TODO Issue #2 (shared places)
    //adjustments for SOUR.DATA.EVEN
    $extend2 = !class_exists("Fisharebest\Webtrees\Functions\FunctionsEdit", false);
    if ($extend2) {
        $classMap["Fisharebest\Webtrees\Functions\FunctionsEdit"] = __DIR__ . '/replacedWebtrees/Functions/FunctionsEdit.php';
    }

    //FunctionsEdit also has adjustments for research suggestions, and _FSFTID
    
    $loader->addClassMap($classMap);        
}

$loader->register(true); //prepend in order to override definitions from default class loader