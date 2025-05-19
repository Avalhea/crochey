<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Tag;
use App\Form\TagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('started_at', null, [
                'widget' => 'single_text',
            ])
            ->add('finished_at', null, [
                'widget' => 'single_text',
            ])
            ->add('imageUrl')
            ->add('Status')
            ->add('tags', CollectionType::class, [
                'entry_type' => TagType::class,
                'entry_options' => [
                    'label' => false,
                    'is_embedded' => true
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
