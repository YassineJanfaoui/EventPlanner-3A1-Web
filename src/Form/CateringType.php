<?php

namespace App\Form;

use App\Entity\Catering;
use App\Entity\Venue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class CateringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('MenuType', ChoiceType::class, [
            'choices' => Catering::MENU_TYPE_CHOICES,
            'placeholder' => 'Choose menu type',
        ])
        ->add('NbrPlates')
        ->add('Pricing', NumberType::class, [
            'required' => false,
            'scale' => 2,
            'html5' => true,
            'attr' => [
                'step' => '0.01',
            ],
            'empty_data' => null,
        ])
        ->add('MealSchedule', ChoiceType::class, [
            'choices' => Catering::MEAL_SCHEDULE_CHOICES,
            'placeholder' => 'Choose meal schedule',
        ])
        ->add('Beverages', ChoiceType::class, [
            'choices' => Catering::BEVERAGE_CHOICES,
            'placeholder' => 'Choose beverage',
        ])
        ->add('venue', EntityType::class, [
            'class' => Venue::class,
            'choice_label' => 'VenueName',
            'placeholder' => 'Choose a venue',
        ])
    ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Catering::class,
        ]);
    }
}
