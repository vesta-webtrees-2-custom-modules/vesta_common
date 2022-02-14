<?php

declare(strict_types=1);

namespace Vesta;

use Fisharebest\Webtrees\Http\ViewResponseTrait;

/**
 * Common functions for all controllers
 *
 * @deprecated since 2.0.11 - will be removed in 2.1.0 - use RequestHandlers instead of Controllers
 */
abstract class AbstractBaseController
{
    use ViewResponseTrait;
}
