<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'minlength' => 2,
                    'maxlength' => 100,
                    'required' => true,
                    'class' => 'form-control rounded-pill px-4 py-2',
                    'pattern' => '^[a-zA-Z\s]+$',
                    'title' => 'Only letters and spaces are allowed'
                ]
            ])
            ->add('affiliation', null, [
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'form-control rounded-pill px-4 py-2'
                ],
                'required' => false,
            ])
            ->add('age', null, [
                'attr' => [
                    'min' => 5,
                    'max' => 99,
                    'class' => 'form-control rounded-pill px-4 py-2'
                ],
                'required' => false,
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'teamname',
                'attr' => [
                    'class' => 'form-select rounded-pill px-4 py-2'
                ],
                'placeholder' => 'Select a team',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}