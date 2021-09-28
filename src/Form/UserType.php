<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class UserType extends AbstractType
{
	private bool $askPassword;
	
	public function __construct(private AuthorizationChecker $authorization) {}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$this->askPassword = $options['askPassword'];
		$builder->add('username', TextType::class, ['label' => "Nom d'utilisateur"]);
		if ($this->askPassword === true) {
			$builder->add('password', RepeatedType::class, [
				'type'            => PasswordType::class,
				'invalid_message' => 'Les deux mots de passe doivent correspondre.',
				'required'        => true,
				'first_options'   => ['label' => 'Mot de passe'],
				'second_options'  => ['label' => 'Tapez le mot de passe à nouveau'],
			]);
		}
		$builder->add('email', EmailType::class, ['label' => 'Adresse email']);
		if ($this->authorization->isGranted('ROLE_ADMIN')) {
			$builder->add('roles', ChoiceType::class, array(
				'choices' => array(
					'Utilisateur'    => 'ROLE_USER',
					'Administrateur' => 'ROLE_ADMIN'
				),
				'label'   => 'Rôle'
			));
			$builder->get('roles')->addModelTransformer(new CallbackTransformer(
				function ($rolesArray) {
					return count($rolesArray) ? $rolesArray[0] : null;
				},
				function ($rolesString) {
					return [$rolesString];
				}
			));
		}
	}
	
	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => User::class,
			'askPassword' => true
		]);
		
		$resolver->setRequired('askPassword'); // Requires that currentOrg be set by the caller.
		$resolver->setAllowedTypes('askPassword', 'boolean'); // Validates the type(s) of option(s) passed.
	}
}
