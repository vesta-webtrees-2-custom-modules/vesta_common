<?php

namespace Vesta\Hook\HookInterfaces;

use Vesta\Model\PlaceStructure;

/**
 * base impl of FunctionsPlaceInterface
 */
trait EmptyFunctionsPlace {
		
	protected $placesOrder = 0;

		public function setPlacesOrder(int $order): void {
			$this->placesOrder = $order;
		}

		public function getPlacesOrder(): int {
			return $this->placesOrder ?? $this->defaultPlacesOrder();
		}

		public function defaultPlacesOrder(): int {
			return 9999;
		}
		
		public function hPlacesGetLatLon(PlaceStructure $place) {
			return null;
		}
		
		public function hPlacesGetParentPlaces(PlaceStructure $place, $typesOfLocation, $recursively = false) {
			return array();
		}
}

?>