<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{
	public function testIndex()
	{
		$client = static::createClient();
		
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->find(1);

		$client->loginUser($testUser);
		
		$client->request('GET', '/');
		
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
	}
	
	public function testIndexWhenNotLoggedIn()
	{
		$client = static::createClient();
		$client->request('GET', '/');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('http://localhost/login');
	}
}
