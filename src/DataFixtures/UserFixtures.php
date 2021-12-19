<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername("usuario")
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$zoGJ621AOwsktV5wAVNMTQ$LuoXX1RT/ti98JcHzwBj9jtFhQKfs67Tm2sAs50Yk6w');

        $manager->persist($user);

        $manager->flush();
    }
}
