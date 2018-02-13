<?php

namespace AppBundle\Utils;

class Jobeet
{
    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);
        // trim
        $text = trim($text, '-');

        if (function_exists('iconv')) {
            //$text = iconv('UTF-8', 'US-ASCII//TRANSLIT', $text);
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        }

        // replace all non letters or digits by -
        $text = preg_replace('/\W+/', '-', $text);

        // trim and lowercase
        $text = strtolower(trim($text, '-'));

        return $text;
    }
}
