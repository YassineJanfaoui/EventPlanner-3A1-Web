<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Venue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class VenueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Location')
            ->add('NbrPlaces')
            ->add('VenueName')
            ->add('Availability', ChoiceType::class, [
                'choices' => array_flip(Venue::AVAILABILITY_CHOICES),
                'placeholder' => 'Choose availability',
            ])
            ->add('Cost')
            ->add('Parking', ChoiceType::class, [
                'choices' => array_flip(Venue::PARKING_CHOICES),
                'placeholder' => 'Choose parking',
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false,
                'attr' => ['readonly' => true],
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false,
                'attr' => ['readonly' => true],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
        ]);
    }
}
