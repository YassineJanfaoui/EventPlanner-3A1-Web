<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Event;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Only add TeamName field if show_team_name is true or not set
        if ($options['show_team_name'] ?? true) {
            $builder
                ->add('TeamName', TextType::class, [
                    'label' => 'Team Name',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter team name'
                    ]
                ]);
        }
        
        $builder
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'placeholder' => 'Select an event',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Event'
            ])
        ;

        // Only add Score and Rank fields if explicitly requested
        if ($options['show_score_rank'] ?? false) {
            $builder
                ->add('Score', NumberType::class, [
                    'required' => false
                ])
                ->add('Rank', IntegerType::class, [
                    'required' => false
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
            'show_score_rank' => false,
            'show_team_name' => true
        ]);
    }
}
