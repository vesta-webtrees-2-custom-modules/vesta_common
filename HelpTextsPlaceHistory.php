<?php

namespace Vesta;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\I18N;
use function view;

class HelpTextsPlaceHistory {

    public static function helpText($help) {
        switch ($help) {

            case 'Predecessor':
                $title = I18N::translate('Relationship to predecessor');
                $text = '<p>' .
                    I18N::translate('The relationship refers to the predecessor, i.e. the individual from the preceding fact of the same type.') .
                    '</p>';
                break;

            default:
                $title = MoreI18N::xlate('Help');
                $text = MoreI18N::xlate('The help text has not been written for this item.');
                break;
        }

        return view('modals/help', [
            'title' => $title,
            'text' => $text,
        ]);
    }

}
