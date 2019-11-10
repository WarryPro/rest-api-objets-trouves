<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $user = new User();
        // $manager->persist($user);

        // create 20 items! Bam!
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setFirstname('user '.$i);
            $user->setEmail('user'.$i.'@mail.com');
            $user->setPassword('password'.$i);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
