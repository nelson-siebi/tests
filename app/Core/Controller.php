<?php

namespace App\Core;

class Controller
{
    public function view($name, $data = [])
    {
        extract($data);
        require_once __DIR__ . '/../Views/' . $name . '.php';
    }
}
