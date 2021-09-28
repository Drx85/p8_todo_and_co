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
	
	public function testRedirectLoginWithBadPassword()
	{
		$this->fillAndSubmitBadPassword($this->client);
		$this->assertResponseRedirects('/login');
	}
	
	public function testFlashMessageLoginWithBadPassword()
	{
		$this->client->followRedirects(true);
		$this->fillAndSubmitBadUsername($this->client);
		$this->assertSelectorExists('.alert.alert-danger');
	}
	
	public function testRedirectLoginWithBadUsername()
	{
		$this->fillAndSubmitBadPassword($this->client);
		$this->assertResponseRedirects('/login');
	}
	
	public function testFlashMessageLoginWithBadUsername()
	{
		$this->client->followRedirects(true);
		$this->fillAndSubmitBadUsername($this->client);
		$this->assertSelectorExists('.alert.alert-danger');
	}
	
	public function testRedirectLoginWithBadUsernameAndPassword()
	{
		$this->fillAndSubmitBadUsernameAndPassword($this->client);
		$this->assertResponseRedirects('/login');
	}
	
	public function testFlashMessageLoginWithBadUsernameAndPassword()
	{
		$this->client->followRedirects(true);
		$this->fillAndSubmitBadUsernameAndPassword($this->client);
		$this->assertSelectorExists('.alert.alert-danger');
	}
	
	public function testRedirectLoginWithNotCsrfToken()
	{
		$this->client->request('POST', '/login', [
			'username' => 'test',
			'password' => 'testtest'
		]);
		$this->assertResponseRedirects('/login');
	}
	
	public function testFlashMessageLoginWithNotCsrfToken()
	{
		$this->client->followRedirects(true);
		$this->client->request('POST', '/login', [
			'username' => 'test',
			'password' => 'testtest'
		]);
		$this->assertSelectorExists('.alert.alert-danger');
	}
	
	public function testSuccessfullLogin()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/user.yaml']);
		$crawler = $this->client->request('GET', '/login');
		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'test',
			'password' => 'testtest'
		]);
		$this->client->submit($form);
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
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
	
	private function fillAndSubmitBadPassword($client)
	{
		$crawler = $client->request('GET', '/login');
		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'test',
			'password' => 'badpassword'
		]);
		$client->submit($form);
	}
	
	private function fillAndSubmitBadUsername($client)
	{
		$crawler = $client->request('GET', '/login');
		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'badusername',
			'password' => 'testtest'
		]);
		$client->submit($form);
	}
	
	private function fillAndSubmitBadUsernameAndPassword($client)
	{
		$crawler = $client->request('GET', '/login');
		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'badusername',
			'password' => 'badpassword'
		]);
		$client->submit($form);
	}
}
