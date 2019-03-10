<?php
namespace Vesta;

use Fisharebest\Webtrees\Webtrees;

//webtrees major version switch
if (defined("WT_VERSION")) {
	//this is a webtrees 2.x module. it cannot be used with webtrees 1.x. See README.md.
	return;
} else {
	$version = Webtrees::VERSION;
}

require_once __DIR__ . '/autoload.php';

return new VestaCommonLibModule2('vestaCommonLib');
