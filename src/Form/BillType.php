<?php

namespace App\Form;

use App\Entity\Bill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('billid')
            ->add('PaymentStatus', null, [
                'widget' => 'single_text'
            ])
            ->add('Amount')
            ->add('Description')
            ->add('Archived')
            ->add('EventId')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bill::class,
        ]);
    }
}
