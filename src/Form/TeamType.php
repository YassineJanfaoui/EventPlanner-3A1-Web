<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Only add TeamName field if show_team_name is true
        if ($options['show_team_name']) {
            $builder
                ->add('TeamName', TextType::class, [
                    'label' => 'Team Name',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter team name'
                    ],
                    'label_attr' => [
                        'class' => 'form-label'
                    ]
                ]);
        }
            
        // Only add Score and Rank fields if show_score_rank is true
        if ($options['show_score_rank']) {
            $builder
                ->add('Score', NumberType::class, [
                    'label' => 'Score',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter team score'
                    ],
                    'label_attr' => [
                        'class' => 'form-label'
                    ],
                    'required' => true,
                ])
                ->add('Rank', NumberType::class, [
                    'label' => 'Rank',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter team rank'
                    ],
                    'label_attr' => [
                        'class' => 'form-label'
                    ],
                    'required' => true,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
            'show_score_rank' => true,  // By default, show score and rank fields
            'show_team_name' => true,   // By default, show team name field
        ]);
        
        // Define the new options
        $resolver->setAllowedTypes('show_score_rank', 'bool');
        $resolver->setAllowedTypes('show_team_name', 'bool');
    }
}
