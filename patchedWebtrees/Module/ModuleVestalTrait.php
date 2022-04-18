<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Module;

use Cissee\WebtreesExt\Contracts\FunctionsVestals;
use Vesta\Model\PlaceStructure;
use Vesta\Model\VestalRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use function response;
use function route;

/**
 * Trait ModuleVestalTrait - default implementation of ModuleVestalInterface
 */
trait ModuleVestalTrait {

    public function useVestals(): bool {    
        return true;
    }
    
    public function hideCoordinates(): bool {    
        return true;
    }
    
    public function vestalsActionUrl(): string {
        $parameters = [
            'module' => $this->name(),
            'action' => 'Vestals'
        ];

        $url = route('module', $parameters);

        return $url;
    }
  
    public function postVestalsAction(
        ServerRequestInterface $request): ResponseInterface {
        
        //request->getBody returns stream, must be converted to string if using strict types!
        $body = json_decode(''.$request->getBody());

        $responses = [];

        foreach ($body as $vestalRequestStd) {
            $method = VestalRequest::methodFromStd($vestalRequestStd);
            $placeStructure = PlaceStructure::fromStd($vestalRequestStd->args);

            if ('vestalBeforePlace' == $method) {
                $response = $this->functionsVestals()->vestalBeforePlace($placeStructure);
                $responses[$response->classAttr()] = $response;
            } else if ('vestalAfterMap' == $method) {
                $response = $this->functionsVestals()->vestalAfterMap($placeStructure);
                $responses[$response->classAttr()] = $response;
            } else if ('vestalAfterNotes' == $method) {
                $response = $this->functionsVestals()->vestalAfterNotes($placeStructure);
                $responses[$response->classAttr()] = $response;
            } else if ('vestalMapCoordinates' == $method) {
                $response = $this->functionsVestals()->vestalMapCoordinates($placeStructure);
                $responses[$response->classAttr()] = $response;
            } else {
                error_log("unexpected method:".$method);
            }
        }
    
        ob_start();
        //array_values required for sequential numeric indexes, otherwise we end up with json object
        echo json_encode(array_values($responses));
        return response(ob_get_clean());
    }
    
    public function functionsVestals(): FunctionsVestals {  
        return new FunctionsVestals(
                $this,
                $this->vestalsActionUrl(),
                $this->useVestals(),
                $this->hideCoordinates());
    }
}
