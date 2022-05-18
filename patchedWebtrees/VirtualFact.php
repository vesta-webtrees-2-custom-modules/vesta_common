<?php

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Fact;

//TODO: also use for 'histo'!
class VirtualFact extends Fact {

    public function canEdit(): bool {
        return false;
    }

}
