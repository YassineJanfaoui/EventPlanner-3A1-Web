<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Entity\Venue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reservationDate', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a reservation date.',
                    ]),
                    new Assert\Date([
                        'message' => 'Please enter a valid date.',
                    ]),
                ],
            ])
            ->add('reservationPrice', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter the reservation price.',
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'The price must be a number.',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'The price cannot be negative.',
                    ]),
                ],
            ])
            ->add('venue', EntityType::class, [
                'class' => Venue::class,
                'choice_label' => 'VenueId',
                'placeholder' => 'Choose a venue',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please choose a venue.',
                    ]),
                ],
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'eventId',
                'placeholder' => 'Choose an event',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please choose an event.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}