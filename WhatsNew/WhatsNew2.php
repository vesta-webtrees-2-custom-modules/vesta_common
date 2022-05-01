<?php

namespace Vesta\WhatsNew;

use Cissee\WebtreesExt\WhatsNew\WhatsNewInterface;

class WhatsNew2 implements WhatsNewInterface {

    public function getMessage(): string {
        return "New feature 'Place history' (a tab for regular places and Shared Places). Shows specific facts for a place, such as a list of owners or residents.";
    }

}
