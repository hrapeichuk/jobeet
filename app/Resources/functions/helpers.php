<?php

if (!function_exists('dd')) {
    function dd($var)
    {
        dump($var); die;
    }
}
