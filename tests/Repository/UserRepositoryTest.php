<?php

namespace App\Tests\Repository;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
	protected $databaseTool;
	
	public function setUp(): void
	{
		parent::setUp();
		$this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
	}
	
	public function testCount()
	{
		self::bootKernel();
		
		$this->databaseTool->loadFixtures([UserFixtures::class]);
		
		$users = self::getContainer()->get(UserRepository::class)->count([]);
		$this->assertEquals($users, 10);
	}
	
}