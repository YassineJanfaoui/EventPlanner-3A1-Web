<?php

namespace App\Form;

use App\Entity\Bill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;



class BillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('PaymentStatus', TextType::class, [
                'empty_data' => '',
                'attr' => ['class' => 'form-control']
            ])
            ->add('Amount', IntegerType::class, [
                'empty_data' => 0,
                'attr' => ['class' => 'form-control']
            ])
            ->add('Description', TextType::class, [
                'empty_data' => '',
                'attr' => ['class' => 'form-control']
            ])
            ->add('Archived', ChoiceType::class, [
                'choices' => [
                    'True' => 1,
                    'False' => 0,
                ],
                'empty_data' => null, // Default to False
                'expanded' => true,
                'multiple' => false,
                'label' => 'Archived',
            ])
            ->add('EventId', IntegerType::class, [
                'empty_data' => null,
                'attr' => ['class' => 'form-control']
            ])
            ->add('DueDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false, // Allow empty submissions
                'empty_data' => null, // Set to null when empty
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'YYYY-MM-DD' // Optional placeholder
                ],
                'invalid_message' => 'Please enter a valid date (YYYY-MM-DD)'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bill::class,
        ]);
    }
}
