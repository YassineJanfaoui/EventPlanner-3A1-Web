<?php

namespace App\Form;

use App\Entity\Equipment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
<<<<<<< HEAD
use Symfony\Component\Form\Extension\Core\Type\NumberType;
=======
>>>>>>> molka-venue-catering


class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => ''
            ])
            ->add('state', ChoiceType::class, [
                'choices' => [
                    'Functional' => 'functional',
                    'Maintenance' => 'maintenance',
                    'Unavailable' => 'unavailable',
                ],
                'attr' => ['class' => 'form-control'],
                'empty_data' => 'unavailable',
                'required' => true,
            ])
<<<<<<< HEAD
            ->add('price', IntegerType::class, [
                'empty_data' => 0
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Tools' => 'tools',
                    'IT' => 'IT',
                    'Accomodation' => 'accomodation',
                    'Audio & Visual' => 'Audio & Visual',
                    'Transport' => 'Transport'
                ],
                'required' => true,
                'placeholder' => 'Select a category',
                'empty_data' => null
=======
            ->add('category', TextType::class, [
                'empty_data' => ''
>>>>>>> molka-venue-catering
            ])
            ->add('quantity', IntegerType::class, [
                'empty_data' => 0
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipment::class,
        ]);
    }
}
