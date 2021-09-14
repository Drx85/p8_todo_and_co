<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
	public function __construct(private UserPasswordHasherInterface $passwordHasher)
	{
	}
	
	public function load(ObjectManager $manager)
	{
		for ($i = 0; $i < 10; $i++) {
			$user = new User();
			$user->setEmail("test$i@test.fr")
				->setUsername("test$i")
				->setPassword($this->passwordHasher->hashPassword($user, 'test'));
			$manager->persist($user);
		}
		$manager->flush();
	}
}
