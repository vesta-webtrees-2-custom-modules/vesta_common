<?php

namespace Vesta\WhatsNew;

use Cissee\WebtreesExt\WhatsNew\WhatsNewInterface;

class WhatsNew1 implements WhatsNewInterface {

    public function getMessage(): string {
        return "Vesta Modules: Translations may now be contributed via <a href=\"https://hosted.weblate.org/projects/vesta-webtrees-custom-modules/\">weblate</a>.";
    }

}
