<?php

namespace App\Form;

use App\Entity\SocialRun;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocialRunType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'error_bubbling' => false,
            ])
            ->add('description', null, [
                'error_bubbling' => false,
            ])
            ->add('startingAt', null, [
                'widget' => 'single_text',
                'error_bubbling' => false,
                'attr' => [
                    'min' => (new \DateTimeImmutable('now'))->format('Y-m-d\TH:i'),
                ],
            ])
            ->add('meetingPoint', null, [
                'error_bubbling' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SocialRun::class,
        ]);
    }
}
