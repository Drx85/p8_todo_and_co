<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
	protected $databaseTool;
	protected $client;
	
	public function setUp(): void
	{
		parent::setUp();
		$this->client = static::createClient();
		$this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
	}
	
	public function testDisplayLogin()
	{
		$this->client->request('GET', '/login');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('h1', "Connection");
		$this->assertSelectorNotExists('.alert.alert-danger');
	}
	
	public function testRedirectLoginWithBadCredentials()
	{
		$this->fillAndSubmitBadForm($this->client);
		$this->assertResponseRedirects('/login/invalid-credentials');
	}
	
	public function testFlashMessageLoginWithBadCredentials()
	{
		$this->client->followRedirects(true);
		$this->fillAndSubmitBadForm($this->client);
		$this->assertSelectorExists('.alert.alert-danger');
	}
	
	public function testLoginWithNotCsrfToken()
	{
		$this->client->request('POST', '/login', [
			'username' => 'test',
			'password' => 'badpassword'
		]);
		$this->assertResponseRedirects('/login/invalid-credentials');
	}
	
	public function testSuccessfulLogin()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/user.yaml']);
		$crawler = $this->client->request('GET', '/login');
		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'test',
			'password' => 'testtest'
		]);
		$this->client->submit($form);
		$this->assertResponseRedirects('/');
		$this->assertIsObject(unserialize($this->client->getContainer()->get('session')->get('_security_main')));
	}
	
	public function testLogout()
	{
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->find(1);
		$this->client->loginUser($testUser);

		$this->client->request('GET', '/logout');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('http://localhost/');
		$this->assertIsNotObject(unserialize($this->client->getContainer()->get('session')->get('_security_main')));
	}
	
	private function fillAndSubmitBadForm($client)
	{
		$crawler = $client->request('GET', '/login');
		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'test',
			'password' => 'badpassword'
		]);
		$client->submit($form);
	}
}
