<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Team;
use App\Entity\EventTeam;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Length;

class EventTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'placeholder' => 'Select an event',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select an event',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'teamName',
                'placeholder' => 'Select a team',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a team',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Submission Title',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Title cannot be blank',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Title must be at least {{ limit }} characters long',
                        'max' => 255,
                        'maxMessage' => 'Title cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter submission title'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('fileLink', UrlType::class, [
                'label' => 'File Link',
                'constraints' => [
                    new NotBlank([
                        'message' => 'File link cannot be blank',
                    ]),
                    new Url([
                        'message' => 'The file link "{{ value }}" is not a valid URL',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter URL to your submission file'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('submissionDate', DateType::class, [
                'label' => 'Submission Date',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Submission date cannot be blank',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventTeam::class,
            'attr' => [
                'novalidate' => 'novalidate', // This allows server-side validation to run even if HTML5 validation is enabled
            ],
        ]);
    }
}
