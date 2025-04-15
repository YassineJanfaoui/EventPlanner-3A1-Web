<?php

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Event;
use App\Entity\EventEquipment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EventEquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'YYYY-MM-DD',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'YYYY-MM-DD',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
            ])
            ->add('equipment', EntityType::class, [
                'class' => Equipment::class,
                'choice_label' => 'name',
                'disabled' => $options['equipment_pre_selected'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventEquipment::class,
            'equipment_pre_selected' => true,
            'start_date' => null

        ]);
    }
}
