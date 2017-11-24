<?php

namespace App\Classes;

use Illuminate\Http\Request as HttpRequest;

/**
 * Class Request
 *
 * @method static mixed input(string $key, mixed $default = null)
 * @method static mixed post(string $key = null, mixed $default = null)
 * @method static array file(string $key = null, mixed $default = null)
 * @method static array all(mixed $keys = null)
 * @method static array only(mixed $keys)
 * @method static array keys()
 * @method static bool is(...$patterns)
 * @method static bool isMethod(string $method)
 * @method static bool isEmptyString(string $key)
 * @method static bool filled(mixed $key)
 * @method static bool ajax()
 * @method static bool has(mixed  $key)
 * @method static bool hasAny(...$keys)
 * @method static bool exists(mixed $key)
 * @method static array except(mixed $keys)
 * @method static mixed query(mixed $key = null, mixed $default = null)
 * @method static string ip()
 * @method static string path()
 * @method static mixed server(string $key = null, mixed $default = null)
 */
class Request
{
    public static function __callStatic($method, $args)
    {
        $request = HttpRequest::createFromGlobals();
        return call_user_func_array([$request, $method], $args);
    }
}
