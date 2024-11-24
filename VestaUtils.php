<?php

namespace Vesta;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Webtrees;

class VestaUtils {

    public static function vestaViewsNamespace(): string {
        return 'Vesta_Views_Namespace';
    }

    //replacement for app/Helpers/functions.php/app and app/Webtrees::make method
    public static function get(string $abstract) {
        if (version_compare(Webtrees::VERSION, '2.2.0', '>=')) {
            return Registry::container()->get($abstract);
        }
        return \Illuminate\Container\Container::getInstance()->make($abstract);
    }

    //replacement for functions.php/app and app/Webtrees::set method
    /**
     * Write a value into the container.
     *
     * @param string        $abstract
     * @param string|object $concrete
     */
    public static function set(string $abstract, $concrete): void {
        if (version_compare(Webtrees::VERSION, '2.2.0', '>=')) {
            if (is_string($concrete)) {
                throw new \Exception(); //no longer supported?
            } else {
                //app/Contracts/ContainerInterface
                Registry::container()->set($abstract, $concrete);
            }
        } else {
            if (is_string($concrete)) {
                \Illuminate\Container\Container::getInstance()->bind($abstract, $concrete);
            } else {
                \Illuminate\Container\Container::getInstance()->instance($abstract, $concrete);
            }
        }
    }
}
