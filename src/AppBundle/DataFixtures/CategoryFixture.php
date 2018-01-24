<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixture extends Fixture
{
    public function load(ObjectManager $em)
    {
        $design = new Category();
        $design->setName('Design');

        $programming = new Category();
        $programming->setName('Programming');

        $manager = new Category();
        $manager->setName('Manager');

        $administrator = new Category();
        $administrator->setName('Administrator');

        $em->persist($design);
        $em->persist($programming);
        $em->persist($manager);
        $em->persist($administrator);

        $em->flush();

        // other fixtures can get these objects using the names
        $this->addReference('category-design', $design);
        $this->addReference('category-programming', $programming);
        $this->addReference('category-manager', $manager);
        $this->addReference('category-administrator', $administrator);
    }
}
