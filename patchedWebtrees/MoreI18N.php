<?php

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\I18N;

class MoreI18N {

    //functionally same as I18N::translate,
    //different name prevents gettext from picking this up
    //(intention: use where already expected to be translated via main webtrees)
    public static function xlate(string $message, ...$args): string {
        return I18N::translate($message, ...$args);
    }

    //functionally same as I18N::translateContext,
    //different name prevents gettext from picking this up
    //(intention: use where already expected to be translated via main webtrees)
    public static function xlateContext(string $context, string $message, ...$args): string {
        return I18N::translateContext($context, $message, ...$args);
    }

    //functionally same as I18N::plural,
    //different name prevents gettext from picking this up
    //(intention: use where already expected to be translated via main webtrees)
    public static function plural(string $singular, string $plural, int $count, ...$args): string {
        return I18N::plural($singular, $plural, $count, ...$args);
    }
}
