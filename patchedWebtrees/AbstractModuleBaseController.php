<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Http\Controllers\AbstractBaseController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Common functions for all module controllers
 */
abstract class AbstractModuleBaseController extends AbstractBaseController {

  /** @var string The directory where the module is installed */
  protected $directory;
  protected $moduleName;

  public function __construct(string $directory, string $moduleName) {
    $this->directory = $directory;
    $this->moduleName = $moduleName;
  }

  /**
   * Create a response object from a view.
   *
   * @param string  $view_name
   * @param mixed[] $view_data
   * @param int     $status
   *
   * @return Response
   */
  protected function viewResponse($view_name, $view_data, $status = Response::HTTP_OK): Response {
    // Make the view's data available to the layout.
    $layout_data = $view_data;

    // Render the view
    $layout_data['content'] = ModuleView::make($this->directory, $view_name, $view_data);

    // Insert the view into the layout
    $html = view($this->layout, $layout_data);

    return new Response($html, $status);
  }

  protected function viewMainResponse($view_name, $view_data, $status = Response::HTTP_OK): Response {
    return parent::viewResponse($view_name, $view_data, $status);
  }

}
