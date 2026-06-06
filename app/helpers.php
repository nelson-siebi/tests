<?php

use App\Core\Language;

function __($key)
{
    return Language::get($key);
}

function base_path($path = '')
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function public_path($path = '')
{
    return base_path('public') . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function env($key, $default = null)
{
    return \App\Core\Env::get($key, $default);
}
