<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
	private $databaseTool;
	private $client;
	private $user;
	
	public function setUp(): void
	{
		parent::setUp();
		$this->client = static::createClient();
		$this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
	}
	
	public function testIndexUsers()
	{
		$this->loadUserAndConnectClient();
		$this->client->request('GET', '/users');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('td', "test");
	}
	
	public function testSuccessfullCreateUser()
	{
		$this->loadUserAndConnectClient();
		$crawler = $this->client->request('POST', '/users/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'user[username]' => 'testsuccess',
			'user[password][first]' => 'testsuccess',
			'user[password][second]' => 'testsuccess',
			'user[email]' => 'testsuccess@test.fr'
		]);
		$this->client->submit($form);
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/users');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été ajouté.");
		$this->assertNotNull(self::getContainer()->get(UserRepository::class)->findOneBy(['username' => 'testsuccess']));
	}
	
	public function testCreateUserWithInvalidMatchPassword()
	{
		$this->loadUserAndConnectClient();
		$crawler = $this->client->request('POST', '/users/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'user[username]' => 'testfail',
			'user[password][first]' => 'testfail',
			'user[password][second]' => 'testbadpassword',
			'user[email]' => 'testfail@test.fr'
		]);
		$this->client->submit($form);
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('li', "Les deux mots de passe doivent correspondre.");
		$this->assertNull(self::getContainer()->get(UserRepository::class)->findOneBy(['username' => 'testfail']));
	}
	
	public function testSuccessfullEditUser()
	{
		$this->loadUserAndConnectClient();
		$crawler = $this->client->request('POST', '/users/' . $this->user->getId() . '/edit');
		$form = $crawler->selectButton('Modifier')->form([
			'user[username]' => 'editsuccess',
			'user[password][first]' => 'testtest',
			'user[password][second]' => 'testtest'
		]);
		$this->client->submit($form);
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/users');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été modifié");
		$this->assertNotNull(self::getContainer()->get(UserRepository::class)->findOneBy(['username' => 'editsuccess']));
	}
	
	private function loadUserAndConnectClient()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/user.yaml']);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$this->user = $userRepository->findOneBy(['username' => 'test']);
		$this->client->loginUser($this->user);
	}
}