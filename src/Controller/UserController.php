<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
	public function __construct(private UserPasswordHasherInterface $passwordHasher)
	{
	}
 
	#[Route('/users', name: 'user_list')]
	
    public function listAction(UserRepository $repository): Response
	{
		$users = $repository->findAll();
        return $this->render('user/list.html.twig', compact('users'));
    }
	
	#[Route('/users/create', name: 'user_create')]
	
    public function createAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
			$user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }
    
	#[Route('/users/{id}/edit', name: 'user_edit')]
	
    public function editAction(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			$user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
		
	#[Route('/login/invalid-credentials', name: 'invalid-credentials')]
	
	public function invalidCredentials(): Response
	{
		$this->addFlash('error','Informations de connexion incorrectes.');
		return $this->redirectToRoute('homepage');
	}
}
