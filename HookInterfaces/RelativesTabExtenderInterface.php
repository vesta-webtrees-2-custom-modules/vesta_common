<?php

namespace Vesta\Hook\HookInterfaces;

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;

/**
 * Interface for modules which intend to hook into 'Families' tab
 */
interface RelativesTabExtenderInterface {

  public function setRelativesTabUIElementOrder(int $order): void;

  public function getRelativesTabUIElementOrder(): int;

  public function defaultRelativesTabUIElementOrder(): int;

  /**
   *
   * @param Individual $person
   * @return GenericViewElement with optional html to display before the entire tab, and optional script
   */
  public function hRelativesTabGetOutputBeforeTab(Individual $person);

  /**
   *
   * @param Individual $person
   * @return GenericViewElement with html to display after the entire tab, and script
   */
  public function hRelativesTabGetOutputAfterTab(Individual $person);

  /**
   *
   * @param Individual $person
   * @return GenericViewElement with html to display in the description box, and script
   */
  public function hRelativesTabGetOutputInDBox(Individual $person);

  /**
   *
   * @param Individual $person
   * @return GenericViewElement with html to display after the description box, and script
   *
   * The table structure is basically for consistency with the facts and events tab.
   *
   */
  public function hRelativesTabGetOutputAfterDBox(Individual $person);

  /**
   *
   * @param Family $family
   * @param string $type
   * @return GenericViewElement with html to display after a family subheader.
   *
   */
  public function hRelativesTabGetOutputFamAfterSH(Family $family, $type);
}
