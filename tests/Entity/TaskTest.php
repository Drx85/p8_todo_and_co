<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskTest extends KernelTestCase
{
	protected $databaseTool;
	
	public function setUp(): void
	{
		parent::setUp();
		$this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
	}
	
	public function getEntity(): Task
	{
		return (new Task())
			->setCreatedAt(new \DateTimeImmutable())
			->setTitle('titletest')
			->setContent('contenttest')
			->toggle(1);
	}
	
	public function assertHasErrors(Task $task, int $number = 0)
	{
		self::bootKernel();
		$errors = self::getContainer()->get(ValidatorInterface::class)->validate($task);
		$messages = [];
		/** @var ConstraintViolation $error */
		foreach ($errors as $error) {
			$messages[] = $error->getPropertyPath() . '=>' . $error->getMessage();
		}
		$this->assertCount($number, $errors, implode(', ', $messages));
	}
	
	public function testValidEntity()
	{
		$this->assertHasErrors($this->getEntity());
	}
	
	public function testInvalidBlankTitle()
	{
		$this->assertHasErrors($this->getEntity()->setTitle(''), 1);
	}
	
	public function testInvalidBlankContent()
	{
		$this->assertHasErrors($this->getEntity()->setContent(''), 1);
	}
}