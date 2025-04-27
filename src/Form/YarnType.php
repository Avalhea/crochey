<?php

namespace App\Form;

use App\Entity\Yarn;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YarnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('color')
            ->add('brand')
            ->add('quantity')
            ->add('imageUrl')
            ->add('notes')
            ->add('addedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('FiberContent')
            ->add('Weight')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Yarn::class,
        ]);
    }
}
