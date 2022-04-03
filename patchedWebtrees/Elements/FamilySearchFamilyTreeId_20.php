<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Elements;

use Fisharebest\Webtrees\Elements\AbstractExternalLink;
use function strtoupper;

class FamilySearchFamilyTreeId_20 extends AbstractExternalLink
{
    protected const EXTERNAL_URL = 'https://www.familysearch.org/tree/person/details/{ID}';

    protected const MAXIMUM_LENGTH = 8;

    /**
     * Convert a value to a canonical form.
     *
     * @param string $value
     *
     * @return string
     */
    public function canonical(string $value): string
    {
        return strtoupper(parent::canonical($value));
    }
}
