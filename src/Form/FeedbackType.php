<?php
 namespace App\Form;
 use App\Entity\Feedback;
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\FormBuilderInterface;
 use Symfony\Component\OptionsResolver\OptionsResolver;
 use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
 use Symfony\Component\Form\Extension\Core\Type\TextareaType;
 use Symfony\Component\Form\Extension\Core\Type\HiddenType;
 use Symfony\Bridge\Doctrine\Form\Type\EntityType;
 use App\Entity\Event;
 use App\Entity\Team;

 class FeedbackType extends AbstractType
 {
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'Your Comment',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Please share your feedback...',
                    'rows' => 4
                ],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('sentiment_pos', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'sentiment-pos-field'
                ]
            ])
            ->add('rate', ChoiceType::class, [
                'label' => 'Rating',
                'choices' => [
                    '1 Star' => 1,
                    '2 Stars' => 2,
                    '3 Stars' => 3,
                    '4 Stars' => 4,
                    '5 Stars' => 5,
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'mapped' => true,
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'TeamName',
                'label' => 'Select Team',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'label' => 'Select Event',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
