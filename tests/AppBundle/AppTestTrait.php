<?php

namespace Tests\AppBundle;

use Doctrine\ORM\Tools\SchemaTool;

trait AppTestTrait
{
    public function refreshDatabaseSchema()
    {
        $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }
        $this->postFixtureSetup();
    }
}
