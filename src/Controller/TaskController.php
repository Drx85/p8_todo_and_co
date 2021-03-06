<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskController extends AbstractController
{
	#[Route('/tasks', name: 'task_list')]
    
    public function listAllTasks(TaskRepository $repository): Response
	{
		$tasks = $repository->findAllWithUsers();
//		$tasks = $repository->findAll();
        return $this->render('task/list.html.twig', compact('tasks'));
    }
	
	#[Route('/tasks-finished', name: 'task_list_finished')]
	
	public function listDoneTasks(TaskRepository $repository): Response
	{
		$tasks = $repository->findByIsDone(true);
		return $this->render('task/list.html.twig', compact('tasks'));
	}
	
	#[Route('/tasks-not-finished', name: 'task_list_not_finished')]
	
	public function listNotDoneTasks(TaskRepository $repository): Response
	{
		$tasks = $repository->findByIsDone(false);
		return $this->render('task/list.html.twig', compact('tasks'));
	}
	
	#[Route('/tasks/create', name: 'task_create')]
	
	public function createAction(Request $request, UserInterface $user)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $task->setUser($user);

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }
        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

	#[Route('/tasks/{id}/edit', name: 'task_edit')]
	
    public function editAction(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        	$task->setUpdatedAt(new \DateTimeImmutable());
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }
        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

	#[Route('/tasks/{id}/toggle', name: 'task_toggle')]
	
    public function toggleTaskAction(Task $task): RedirectResponse
	{
        $task->toggle(!$task->isDone());
		$task->setUpdatedAt(new \DateTimeImmutable());
        $this->getDoctrine()->getManager()->flush();
        if ($task->isDone()) {
			$this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
		} else {
        	$this->addFlash('success', sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle()));
		}
        return $this->redirectToRoute('task_list');
    }
    
	#[Route('/tasks/{id}/delete', name: 'task_delete')]
	
    public function deleteTaskAction(Task $task): RedirectResponse
	{
		$this->denyAccessUnlessGranted('task_delete', $task);
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
