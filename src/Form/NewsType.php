<?php

namespace App\Form;

use App\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'error_bubbling' => false,
                'label' => 'Titre',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('description', null, [
                'error_bubbling' => false,
                'label' => 'Contenu',
                'attr' => [
                    'rows' => 10,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
