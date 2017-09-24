<?php

namespace App\Classes;

use Illuminate\Http\Request as HttpRequest;

/**
 * Class Request
 *
 * @method static array|string input(string $key, string|array $default = null)
 * @method static array|string post(string $key = null, string|array $default = null)
 * @method static array all(array|mixed $keys = null)
 * @method static array only(array|mixed $keys)
 * @method static array keys()
 * @method static bool is(...$patterns)
 * @method static bool isMethod(string $method)
 * @method static bool isEmptyString(string $key)
 * @method static bool filled(string|array $key)
 * @method static bool ajax()
 * @method static bool has(string|array  $key)
 * @method static bool hasAny(...$keys)
 * @method static bool exists(string|array $key)
 * @method static array except(array|mixed $keys)
 * @method static array|string query(string|array $key = null, string|array $default = null)
 * @method static string ip()
 * @method static string path()
 * @method static array|string server(string $key = null, string|array $default = null)
 */
class Request
{
    public static function __callStatic($method, $args)
    {
        $request = HttpRequest::createFromGlobals();
        return call_user_func_array([$request, $method], $args);
    }
}
