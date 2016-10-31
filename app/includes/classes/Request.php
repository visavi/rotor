<?php

use Illuminate\Http\Request as HttpRequest;

class Request {
    public static function __callStatic($method, $args)
    {
        $request = HttpRequest::createFromGlobals();
        return call_user_func_array([$request, $method], $args);
    }
}
