<?php

namespace Vesta;

//webtrees major version switch
if (defined("WT_VERSION")) {
    //this is a webtrees 2.x module. it cannot be used with webtrees 1.x. See README.md.
    return;
}

require_once __DIR__ . '/autoload.php';

$placeholder = \Vesta\VestaUtils::get(PlaceholderModule::class);
return $placeholder->ifIncompatible() ?? \Vesta\VestaUtils::get(VestaCommonLibModule::class);
