<?php
namespace Core\Security\User;

use Backoffice\Entity\Repository\ProjectRepository;
use Core\Security\Group\GroupRepository;
use Core\Validator\Unique;
use Core\Validator\ContainsAlphanumeric;
use Doctrine\DBAL\Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Silex\Translator;
use Symfony\Component\Validator\Constraints\Ip;

class UserType extends AbstractType
{
	/**
	 * @var GroupRepository
	 * @inject
	 */
	protected $groupRepository;
	/**
	 * @var ProjectRepository
	 * @inject
	 */
	protected $projectRepository;

	/**
	 * @var Connection
	 * @inject
	 */
	protected $db;
	/**
	 * @var Translator
	 * @inject
	 */
	protected $translator;

	public function buildForm(FormBuilderInterface $builder, array $options = [])
	{
		$builder
			->add('id', 'hidden')
			->add('full_name', 'text')
			->add('description', 'textarea')
            ->add(
                'is_hidden',
                'checkbox',
                [
                    'required' => false
                ]
            )
			->add(
			// TODO: remake this to treat ip_whitelist as array
				'ipWhitelist',
/*				'text',
				[
					'constraints' => [
						new Ip()
					]
				]*/
                'collection',
				[
					'type' => 'text',
					'required' => false,
					'allow_add' => true,
					'allow_delete' => true,
					'prototype' => true,
					'label' => '',
					'options' => [
						'constraints' => [
							new Ip()
						]
					]
				]
			)
			->add(
				'groups',
				'choice',
				[
					'choices' => $this->groupRepository->findAll(),
					'multiple' => true,
					'expanded' => true,
				]
			)
			->add(
				'projects',
				'choice',
				[
					'choices' => $this->projectRepository->findAll(),
					'multiple' => true,
					'expanded' => true
				]
			)
			->add('save', 'submit')
			->setAction('/backoffice/users/save')
			->setMethod('POST')
			->getForm();

		$builder->addEventListener(
			FormEvents::PRE_SET_DATA,
			function (FormEvent $event) {
				/** @var User $user */
				$user = $event->getData();
				$form = $event->getForm();
				if (isset($user) && !$user->isNew()) {
					$form->add(
						'username',
						'text',
						[
							'disabled' => true,
						]
					);
					$form->add(
						'email',
						'email',
						[
							'disabled' => true,
						]
					);
				} else {
					$form->add(
						'username',
						'text',
						[
							'constraints' => [
                                new ContainsAlphanumeric([
                                    'field' => 'username',
                                    'translator' => $this->translator
                                ]),
								new Unique(
									[
										'db' => $this->db,
										'table' => 'users',
										'field' => 'username',
										'translator' => $this->translator
									]
								)
							]
						]
					);
					$form->add(
						'email',
						'email',
						[
							'constraints' => [
								new Unique(
									[
										'db' => $this->db,
										'table' => 'users',
										'field' => 'email',
										'translator' => $this->translator
									]
								)
							]
						]
					);
					$form->add(
						'password',
						'text',
						[
//							'type' => 'password',
//							'invalid_message' => 'Passwords must match',
							'required' => true,
//							'first_options' => [
//								'label' => 'Password',
//							],
//							'second_options' => [
//								'label' => 'Repeat password'
//							]
						]
					);
				}
			}
		);

		return $builder;
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName()
	{
		return 'user';
	}

	public function getDefaultOptions()
	{
		return [
			'data_class' => User::class,
			'csrf_protection' => false,
			'allow_extra_fields' => true
		];
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults($this->getDefaultOptions());
	}
}