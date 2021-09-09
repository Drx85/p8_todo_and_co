<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{
	private $databaseTool;
	private $client;
	
	public function setUp(): void
	{
		parent::setUp();
		$this->client = static::createClient();
		$this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
	}
	
	public function testIndex()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/user.yaml']);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->findOneBy(['username' => 'test']);

		$this->client->loginUser($testUser);
		
		$this->client->request('GET', '/');
		
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
	}
	
	public function testIndexWhenNotLoggedIn()
	{
		$this->client->request('GET', '/');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('http://localhost/login');
	}
}
