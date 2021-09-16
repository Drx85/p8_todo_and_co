<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
	public function __construct(private UserRepository $userRepository) {}
	
    public function load(ObjectManager $manager)
	{
		
		$titlesList = new \SplFileObject('src/DataFixtures/Provider/titles.txt', 'r');
		$content = new \SplFileObject('src/DataFixtures/Provider/content.txt', 'r');

		$users = $this->userRepository->findAll();
		for ($i = 0; $i < 20; $i++) {
			$task = new Task();
			$task->setUser($users[mt_rand(0, 11)])
				->setTitle($titlesList->fgets())
				->setContent($content->fgets())
				->toggle((bool) mt_rand(0, 1));

			$manager->persist($task);
		}
		$titlesList = null;
		$content = null;
		$manager->flush();
	}
	
	public function getDependencies(): array
	{
		return [
			UserFixtures::class,
		];
	}
}
