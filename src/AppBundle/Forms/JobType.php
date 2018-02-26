<?php

namespace AppBundle\Forms;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, ['class' => Category::class, 'choice_label' => 'name'])
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => Job::TYPES,
                    'choice_label' => function ($value) {
                        return 'types.'.$value;
                    },
                    'expanded' => true,
                    'constraints' => [new Length(['min' => 3])],
                    'label_attr' => ['class' => 'radio-inline'],
                ]
            )
            ->add('company')
            ->add('logo', FileType::class, ['required' => false, 'label' => 'Company logo', 'data_class' => null])
            ->add('url', null, ['required' => false])
            ->add('position', null, ['constraints' => new NotBlank()])
            ->add('location', null, ['constraints' => new NotBlank()])
            ->add('description', null, ['constraints' => new NotBlank()])
            ->add('howToApply', null, [
                'label' => 'How to apply?',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('isPublic', null, ['label' => 'Public'])
            ->add('email', null, [
                'constraints' => [
                    new Email(),
                    new NotBlank(),
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Job::class
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'job';
    }
}
