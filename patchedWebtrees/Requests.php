<?php

namespace Cissee\WebtreesExt;

use Psr\Http\Message\ServerRequestInterface;

class Requests {

    public static function getInt(ServerRequestInterface $request, string $var, int $default = 0) {
        $ret = $request->getAttribute($var) ?? $request->getQueryParams()[$var] ?? $request->getParsedBody()[$var] ?? $default;
        return intval($ret);
    }

    public static function getIntOrNull(ServerRequestInterface $request, string $var) {
        $raw = $request->getAttribute($var) ?? $request->getQueryParams()[$var] ?? $request->getParsedBody()[$var] ?? null;
        $ret = ($raw === null) ? null : intval($raw);
        return $ret;
    }

    public static function getBool(ServerRequestInterface $request, string $var, bool $default = false) {
        $ret = $request->getAttribute($var) ?? $request->getQueryParams()[$var] ?? $request->getParsedBody()[$var] ?? $default;
        return boolval($ret);
    }

    public static function getString(ServerRequestInterface $request, string $var, string $default = '') {
        return $request->getAttribute($var) ?? $request->getQueryParams()[$var] ?? $request->getParsedBody()[$var] ?? $default;
    }

    public static function getStringOrNull(ServerRequestInterface $request, string $var) {
        return $request->getAttribute($var) ?? $request->getQueryParams()[$var] ?? $request->getParsedBody()[$var] ?? null;
    }

    public static function getArray(ServerRequestInterface $request, string $var, $default = []) {
        $ret = $request->getAttribute($var) ?? $request->getQueryParams()[$var] ?? $request->getParsedBody()[$var] ?? $default;
        return (array) $ret;
    }

}
