<?php

namespace Tests\AppBundle;

use Doctrine\ORM\Tools\SchemaTool;

trait AppTestTrait
{
    /** @return \Doctrine\ORM\EntityManager */
    abstract public function getEntityManager();

    public function refreshDatabaseSchema()
    {
        $metadatas = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }
        $this->postFixtureSetup();
    }
}
