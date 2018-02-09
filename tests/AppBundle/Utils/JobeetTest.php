<?php

namespace Tests\AppBundle\Utils;

use AppBundle\Utils\Jobeet;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * @IgnoreAnnotation("dataProvider")
 * @IgnoreAnnotation("depends")
 */
class JobeetTest extends TestCase
{
    public function slugifyDataProvider()
    {
        return [
            ['okay-google', 'Okay Google'],
            ['sensio-labs', 'sensio labs'],
            ['sensio-labs', 'sensio   labs'],
            ['paris-france', 'paris,france'],
            ['sensio', '  sensio'],
            ['sensio', 'sensio  '],
            ['', ''],
            ['developpeur-web', 'Développeur Web'],
        ];
    }

    /**
     * @dataProvider slugifyDataProvider
     * @param $expected
     * @param $slug
     */
    public function testSlugify($expected, $slug)
    {
        $this->assertEquals($expected, Jobeet::slugify($slug));
    }
}
