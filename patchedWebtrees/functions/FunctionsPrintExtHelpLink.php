<?php

namespace Cissee\WebtreesExt\Functions;

use Cissee\WebtreesExt\MoreI18N;
use function route;
use function view;

class FunctionsPrintExtHelpLink {

  //cf helpLink in FunctionsPrint
  public static function helpLink($module, $topic) {
    $url = route('module', [
        'module' => $module,
        'action' => 'Help',
        'topic' => $topic
    ]);

    return
            '<a href="#" data-toggle="modal" data-target="#wt-ajax-modal" data-href="' . $url . '" title="' . MoreI18N::xlate('Help') . '">' .
            view('icons/help') .
            '<span class="sr-only">' . MoreI18N::xlate('Help') . '</span>' .
            '</a>';
  }

}
