<?php
 namespace App\Form;
 use App\Entity\Feedback;
 use Symfony\Component\Form\AbstractType;
 use Symfony\Component\Form\FormBuilderInterface;
 use Symfony\Component\OptionsResolver\OptionsResolver;
 use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
 use Symfony\Component\Form\Extension\Core\Type\TextareaType;
 use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Assuming Event and Team are entities
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
            ->add('rate', ChoiceType::class, [
                'label' => 'Rating', // Label for the hidden select
                'choices' => [
                    // The keys (1, 2, 3, 4, 5) MUST match the data-value in the HTML
                    // The values ('1 Star', '2 Stars', etc.) are less critical here as the select is hidden,
                    // but good for clarity if it were visible.
                    '1 Star' => 1,
                    '2 Stars' => 2,
                    '3 Stars' => 3,
                    '4 Stars' => 4,
                    '5 Stars' => 5,
                ],
                'expanded' => false, // Render as a <select> element
                'multiple' => false, // Only one choice allowed
                'required' => true, // Or false, depending on your needs
                // Add constraints if needed
                // 'constraints' => [ ... ],
                // Ensure it maps correctly to your entity property
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

    // Uncomment the configureOptions method
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
} // Corrected closing brace
