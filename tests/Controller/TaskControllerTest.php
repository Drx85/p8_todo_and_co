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
	private $testUser;
	
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
			['/tasks-finished'],
			['/tasks-not-finished'],
			['/tasks/create'],
			['/tasks/1/edit'],
			['/tasks/1/toggle'],
			['/tasks/1/delete']
		];
	}
	
	public function testIndexAllTasks()
	{
		$this->loadTaskAndConnectClient();
		$this->client->request('GET', '/tasks');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorExists('.card');
	}
	
	public function testIndexDoneTasks()
	{
		$this->loadTaskAndConnectClient();
		$this->client->request('GET', '/tasks-finished');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorExists('.card');
	}
	
	public function testIndexNotDoneTasks()
	{
		$this->loadAnonymousNotDoneTaskAndConnectClient();
		$this->client->request('GET', '/tasks-not-finished');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorExists('.card');
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
	
	public function testSuccessfullEditTaskAsUserLinkedToTask()
	{
		$this->loadTaskAndConnectClient();
		$this->testSuccessfullEditTask();
	}
	
	public function testSuccessfullEditTaskAsUserNotLinkedToTask()
	{
		$this->loadTaskAndConnectClient('user');
		$this->testSuccessfullEditTask();
	}
	
	public function testSuccessfullEditTaskAsAdminNotLinkedToTask()
	{
		$this->loadTaskAndConnectClient('admin');
		$this->testSuccessfullEditTask();
	}
	
	public function testSuccessfullToggleTask()
	{
		$this->loadTaskAndConnectClient();
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/toggle');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "La tâche " . $this->task->getTitle() . " a bien été marquée comme non terminée.");
		$updatedTask = self::getContainer()->get(TaskRepository::class)->findOneBy(['title' => 'test']);
		$this->assertTrue(!$updatedTask->isDone());
	}
	
	public function testSuccessfullReverseToggleTask()
	{
		$this->loadAnonymousNotDoneTaskAndConnectClient();
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
		$this->loadTaskAndConnectClient('user');
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/delete');
		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
	}
	
	public function testInvalidDeleteTaskWithAdminNotLinkedToIt()
	{
		$this->loadTaskAndConnectClient('admin');
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/delete');
		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
	}
	
	public function testSuccessfullDeleteAnonymousTaskAsAdmin()
	{
		$this->loadAnonymousNotDoneTaskAndConnectClient();
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/delete');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$this->client->followRedirect();
		$this->assertSelectorTextContains('.alert-success', "La tâche a bien été supprimée.");
		$this->assertNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['content' => 'editsuccess']));
	}
	
	public function testInvalidDeleteAnonymousTaskAsUser()
	{
		$this->loadAnonymousNotDoneTaskAndConnectClient(false);
		$this->client->request('POST', '/tasks/' . $this->task->getId() . '/delete');
		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
	}
	
	/**
	 * @param bool|string $notLinkedToTask
	 */
	private function loadTaskAndConnectClient(bool|string $notLinkedToTask = false)
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/task_user.yaml']);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = match ($notLinkedToTask) {
			false => $userRepository->findOneBy(['username' => 'test']),
			'user' => $userRepository->findOneBy(['username' => 'user_not_linked']),
			'admin' => $userRepository->findOneBy(['username' => 'admin'])
		};
		$userRepository = static::getContainer()->get(TaskRepository::class);
		$this->task = $userRepository->findOneBy(['title' => 'test']);
		$this->client->loginUser($testUser);
	}
	
	private function loadAnonymousNotDoneTaskAndConnectClient(bool $asAdmin = true)
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/task_user.yaml']);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$this->testUser = $userRepository->findOneBy(['username' => 'admin']);
		if ($asAdmin === false) $this->testUser = $userRepository->findOneBy(['username' => 'test']);
		$userRepository = static::getContainer()->get(TaskRepository::class);
		$this->task = $userRepository->findOneBy(['title' => 'anonymous_task']);
		$this->client->loginUser($this->testUser);
	}
	
	private function testSuccessfullEditTask()
	{
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
		$taskByTitle = self::getContainer()->get(TaskRepository::class)->findOneBy(['title' => 'editsuccess']);
		$this->assertNotNull($taskByTitle);
		$this->assertNotNull(self::getContainer()->get(TaskRepository::class)->findOneBy(['content' => 'editsuccess']));
		$this->assertNotEquals($taskByTitle->getUser(), $this->testUser);
	}
}