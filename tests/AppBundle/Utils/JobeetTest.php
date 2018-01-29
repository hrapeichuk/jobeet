<?php

namespace Tests\AppBundle\Utils;

use AppBundle\Utils\Jobeet;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class JobeetTest extends TestCase
{
    public function testSlugify()
    {
        $this->assertEquals('okay-google', Jobeet::slugify('Okay Google'));
        $this->assertEquals('sensio-labs', Jobeet::slugify('sensio labs'));
        $this->assertEquals('sensio-labs', Jobeet::slugify('sensio   labs'));
        $this->assertEquals('paris-france', Jobeet::slugify('paris,france'));
        $this->assertEquals('sensio', Jobeet::slugify('  sensio'));
        $this->assertEquals('sensio', Jobeet::slugify('sensio  '));
        $this->assertEquals('', Jobeet::slugify(''));
        $this->assertEquals('developpeur-web', Jobeet::slugify('Développeur Web'));
    }
}
