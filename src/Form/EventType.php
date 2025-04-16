<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Event Name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The event name is required.',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'The name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'The name cannot be longer than {{ limit }} characters.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startDate', TextType::class, [
                'label' => 'Start Date (format YYYY-MM-DD)',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The start date is required.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\\d{4}-\\d{2}-\\d{2}$/',
                        'message' => 'The date format must be YYYY-MM-DD.',
                    ]),
                    new Assert\Callback(function ($date, $context) {
                        if ($date) {
                            $today = new \DateTime();
                            $today->setTime(0, 0, 0);
                            $inputDate = \DateTime::createFromFormat('Y-m-d', $date);
                            if (!$inputDate || $inputDate < $today) {
                                $context->buildViolation('The start date must be after today.')
                                    ->addViolation();
                            }
                        }
                    })
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endDate', TextType::class, [
                'label' => 'End Date (format YYYY-MM-DD)',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The end date is required.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\\d{4}-\\d{2}-\\d{2}$/',
                        'message' => 'The date format must be YYYY-MM-DD.',
                    ]),
                    new Assert\Callback(function ($date, $context) {
                        $startDate = $context->getRoot()->get('startDate')->getData();
                        if ($date && $startDate) {
                            $start = \DateTime::createFromFormat('Y-m-d', $startDate);
                            $end = \DateTime::createFromFormat('Y-m-d', $date);
                            if ($end < $start) {
                                $context->buildViolation('The end date must be after the start date.')
                                    ->addViolation();
                            }
                        }
                    })
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('maxParticipants', NumberType::class, [
                'label' => 'Maximum Participants',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Maximum participants is required.',
                    ]),
                    new Assert\Positive([
                        'message' => 'The number must be positive.',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 1000,
                        'message' => 'The maximum participants cannot exceed 1000.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Description is required.',
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'The description must be at least {{ limit }} characters long.',
                        'maxMessage' => 'The description cannot be longer than {{ limit }} characters.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('fee', NumberType::class, [
                'label' => 'Participation Fee',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'The fee is required.',
                    ]),
                    new Assert\PositiveOrZero([
                        'message' => 'The fee must be a positive number or zero.',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 1000,
                        'message' => 'The fee cannot exceed 1000.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Event Image',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'An image is required.',
                    ]),
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG or GIF).',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'userid',
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}