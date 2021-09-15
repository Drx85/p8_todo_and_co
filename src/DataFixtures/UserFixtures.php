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
		$usersList = fopen('src/DataFixtures/Provider/user.txt', 'r');
		$passwordsList = fopen('src/DataFixtures/Provider/password.txt', 'r');
		for ($i = 0; $i < 10; $i++) {
			$user = new User();
			$username = fgets($usersList);
			$user->setEmail(strtolower(trim($username)) . "@todo.fr")
				->setUsername($username)
				->setPassword($this->passwordHasher->hashPassword($user, fgets($passwordsList)));
			$manager->persist($user);
		}
		fclose($usersList);
		fclose($passwordsList);
		
		//Create 1 admin account, you can set your username, mail, and password
		$user = new User();
		$user->setUsername('admin')
			->setEmail('your-email@gmail.com')
			->setRoles((array)'ROLE_ADMIN')
			->setPassword($this->passwordHasher->hashPassword($user, 'demo'));
		$manager->persist($user);
		
		//Create 1 anonymous account, which is linked to old tasks (before link user to task system was implemented)
		$user = new User();
		$user->setUsername('anonyme')
			->setEmail('anonyme@todo.fr')
			->setPassword($this->passwordHasher->hashPassword($user, 'demo'));
		$manager->persist($user);
		$manager->flush();
	}
}
