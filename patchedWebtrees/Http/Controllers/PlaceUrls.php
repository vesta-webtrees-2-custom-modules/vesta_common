<?php

namespace Cissee\WebtreesExt\Http\Controllers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Place;
use Illuminate\Support\Collection;

class PlaceUrls {

    /** @var ModuleListInterface|null */
    protected $urlModule;
    protected $urlFilters;

    /** @var Collection<PlaceHierarchyParticipant> */
    protected $participants;

    public function __construct(
        ?ModuleListInterface $urlModule,
        array $urlFilters,
        Collection $participants) {

        $this->urlModule = $urlModule;
        $this->urlFilters = $urlFilters;
        $this->participants = $participants;
    }

    public function url(Place $place): string {
        return $this->getUrl($place, $this->urlFilters);
    }

    protected function getUrl(Place $place, array $urlFilters): string {
        if ($this->urlModule !== null) {
            return $this->urlModule->listUrl($place->tree(), [
                    'place_id' => $place->id(),
                    'tree' => $place->tree()->name(),
                    ] + $urlFilters);
        }

        // The place-list module is disabled...
        return '#';
    }

    /**
     *
     * @return Collection<PlaceHierarchyLinks>
     */
    public function links(Place $place): Collection {
        $links = new Collection();
        foreach ($this->participants as $particpant) {
            $label = $particpant->filterLabel();
            $parameterName = $particpant->filterParameterName();

            if (array_key_exists($parameterName, $this->urlFilters)) {
                $parameterValue = $this->urlFilters[$parameterName];
            } else {
                $parameterValue = -1;
            }

            $mainLabel = null;
            $singleLinks = new Collection();
            if ($parameterValue === -1) {
                $mainLabel = I18N::translate('%1$s filters', $label);

                $nextFilters = [];
                foreach ($this->urlFilters as $key => $value) {
                    $nextFilters[$key] = $value;
                }
                $nextFilters[$parameterName] = 1;
                $singleLinks->add(new PlaceHierarchyLink(I18N::translate('restrict to %1$s', $label), 'icons/expand', $this->getUrl($place, $nextFilters)));

                $nextFilters = [];
                foreach ($this->urlFilters as $key => $value) {
                    $nextFilters[$key] = $value;
                }
                $nextFilters[$parameterName] = 0;
                $singleLinks->add(new PlaceHierarchyLink(I18N::translate('exclude %1$s', $label), 'icons/expand', $this->getUrl($place, $nextFilters)));
            } else if ($parameterValue === 0) {
                $mainLabel = I18N::translate('%1$s excluded', $label);

                $nextFilters = [];
                foreach ($this->urlFilters as $key => $value) {
                    if ($key !== $parameterName) {
                        $nextFilters[$key] = $value;
                    }
                }
                $singleLinks->add(new PlaceHierarchyLink(I18N::translate('reset this filter'), 'icons/collapse', $this->getUrl($place, $nextFilters)));
            } else if ($parameterValue === 1) {
                $mainLabel = I18N::translate('restricted to %1$s', $label);

                $nextFilters = [];
                foreach ($this->urlFilters as $key => $value) {
                    if ($key !== $parameterName) {
                        $nextFilters[$key] = $value;
                    }
                }
                $singleLinks->add(new PlaceHierarchyLink(I18N::translate('reset this filter'), 'icons/collapse', $this->getUrl($place, $nextFilters)));
            }

            $links->add(new PlaceHierarchyLinks($mainLabel, $singleLinks));
        }

        return $links;
    }

}
