<?php

class _Viewer
{
    public function view($view, $data = [])
    {
        extract($data);

        require_once "Views/$view.php";
    }
}