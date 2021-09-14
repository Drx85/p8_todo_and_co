<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
	private $databaseTool;
	private $client;
	private $task;
	
	public function setUp(): void
	{
		parent::setUp();
		$this->client = static::createClient();
		$this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
	}
	
	/**
	 * @dataProvider restrictedUrls
	 */
	public function testAccessTasksPagesWhenNotLoggedIn($url)
	{
		$this->client->request('GET', $url);
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('http://localhost/login');
	}
	
	public function restrictedUrls()
	{
		return [
			['/tasks'],
			['/tasks/create'],
			['/tasks/1/edit'],
			['/tasks/1/toggle'],
			['/tasks/1/delete']
		];
	}
	
	public function testIndexTasks()
	{
		$this->loadTaskAndConnectClient();
		$this->client->request('GET', '/tasks');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorExists('.thumbnail');
	}
	
	public function testSuccessfullCreateTask()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/user.yaml']);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->findOneBy(['username' => 'test']);
		$this->client->loginUser($testUser);
		$crawler = $this->client->request('POST', '/tasks/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'task[title]' => 'testsuccess',
			'task[content]' => 'test'
		]);
		$this->client->submit($form);
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "La tâche a été bien été ajoutée.");
		$this->assertNotNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['title' => 'testsuccess']));
		$this->assertNotNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['user' => $testUser]));
	}
	
	public function testSuccessfullEditTask()
	{
		$this->loadTaskAndConnectClient();
		$crawler = $this->client->request('POST', '/tasks/' . $this->task->getId() . '/edit');
		$form = $crawler->selectButton('Modifier')->form([
			'task[title]' => 'editsuccess',
			'task[content]' => 'editsuccess'
		]);
		$this->client->submit($form);
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "La tâche a bien été modifiée.");
		$this->assertNotNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['title' => 'editsuccess']));
		$this->assertNotNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['content' => 'editsuccess']));
	}
	
	public function testSuccessFullToggleTask()
	{
		$this->loadTaskAndConnectClient();
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/toggle');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "La tâche " . $this->task->getTitle() . " a bien été marquée comme faite.");
		$updatedTask = self::getContainer()->get(TaskRepository::class)->findOneBy(['title' => 'test']);
		$this->assertTrue($updatedTask->isDone());
	}
	
	public function testSuccessfullDeleteTask()
	{
		$this->loadTaskAndConnectClient();
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/delete');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "La tâche a bien été supprimée.");
		$this->assertNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['content' => 'editsuccess']));
	}
	
	public function testInvalidDeleteTaskWithUserNotLinkedToIt()
	{
		$this->loadTaskAndConnectClient(true);
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/delete');
		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
	}
	
	public function testSuccessfullDeleteAnonymousTask()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/task_user.yaml']);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->findOneBy(['username' => 'admin']);
		$userRepository = static::getContainer()->get(TaskRepository::class);
		$this->task = $userRepository->findOneBy(['title' => 'anonymous_task']);
		$this->client->loginUser($testUser);
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/delete');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "La tâche a bien été supprimée.");
		$this->assertNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['content' => 'editsuccess']));
	}
	
	/**
	 * @param bool $userNotLinkedToTask
	 */
	private function loadTaskAndConnectClient(bool $userNotLinkedToTask = false)
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/task_user.yaml']);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->findOneBy(['username' => 'test']);
		if ($userNotLinkedToTask) $testUser = $userRepository->findOneBy(['username' => 'user_not_linked']);
		
		$userRepository = static::getContainer()->get(TaskRepository::class);
		$this->task = $userRepository->findOneBy(['title' => 'test']);
		
		$this->client->loginUser($testUser);
	}
}