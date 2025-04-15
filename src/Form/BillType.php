<?php

namespace App\Form;

use App\Entity\Bill;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('DueDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false, // Allow empty submissions
                'empty_data' => null, // Set to null when empty
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'YYYY-MM-DD' // Optional placeholder
                ],
                'invalid_message' => 'Please enter a valid date '
            ])
            ->add('PaymentStatus', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'pending',
                    'Paid' => 'paid',
                ],
                'attr' => ['class' => 'form-control'],
                'required' => true,
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
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
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
