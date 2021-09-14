<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
	const TASK_DELETE = 'task_delete';
	
	public function __construct(private Security $security) {}
	
    protected function supports(string $attribute, $subject): bool
    {
		if ($attribute != self::TASK_DELETE) return false;
		if (!$subject instanceof Task) return false;
		return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
		$user = $token->getUser();
		if (!$user instanceof UserInterface) return false;
		$task = $subject;
		return $this->canDelete($task, $user);
	}
	
	private function canDelete(Task $task, UserInterface $user): bool
	{
		if ($task->getUser()->getUserIdentifier() === 'anonyme' && $this->security->isGranted('ROLE_ADMIN')) return true;
		return $user === $task->getUser();
	}
}
