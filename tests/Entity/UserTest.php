<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
	protected $databaseTool;
	
	public function setUp(): void
	{
		parent::setUp();
		$this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
	}
	
	public function getEntity(): User
	{
		return (new User())
			->setUsername('nametest')
			->setEmail('emailtest@test.fr')
			->setPassword('passwordtest');
	}
	
	public function assertHasErrors(User $user, int $number = 0)
	{
		self::bootKernel();
		$errors = self::getContainer()->get(ValidatorInterface::class)->validate($user);
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
	
	public function testInvalidEmail()
	{
		$this->assertHasErrors($this->getEntity()->setEmail('false-email'), 1);
	}
	
	public function testInvalidBlankEmail()
	{
		$this->assertHasErrors($this->getEntity()->setEmail(''), 1);
	}
	
	public function testInvalidToolongEmail()
	{
		$this->assertHasErrors($this->getEntity()->setEmail('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@test.fr'), 1);
	}
	
	public function testInvalidBlankUsername()
	{
		$this->assertHasErrors($this->getEntity()->setUsername(''), 1);
	}
	
	public function testInvalidToolongUsername()
	{
		$this->assertHasErrors($this->getEntity()->setUsername('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'), 1);
	}
	
	public function testInvalidBlankPassword()
	{
		$this->assertHasErrors($this->getEntity()->setPassword(''), 2);
	}
	
	public function testInvalidTooshortPassword()
	{
		$this->assertHasErrors($this->getEntity()->setPassword('false'), 1);
	}
	
	public function testInvalidToolongPassword()
	{
		$this->assertHasErrors($this->getEntity()->setPassword('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'), 1);
	}
	
	public function testInvalidUsedEmail()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/user.yaml']);
		$this->assertHasErrors($this->getEntity()->setEmail('test'), 1);
	}
	
	public function testInvalidUsedUsername()
	{
		$this->databaseTool->loadAliceFixture([dirname(__DIR__) . '/Fixtures/user.yaml']);
		$this->assertHasErrors($this->getEntity()->setEmail('test'), 1);
	}
}