<?php

namespace App\Form;

use App\Entity\Partner;
use App\Entity\Workshop;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class WorkshopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('coach')
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'empty_data' => null,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'invalid_message' => 'Please enter a valid date (YYYY-MM-DD)'
            ])
            ->add('duration')
            ->add('description')
            ->add('partner', EntityType::class, [
                'class' => Partner::class,
                'choice_label' => 'name', // use a readable property instead of 'id'
                'placeholder' => 'Choose a partner', // optional, adds a blank option
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'placeholder' => 'Select an event',
                'attr' => ['class' => 'form-select']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workshop::class,
        ]);
    }
}
