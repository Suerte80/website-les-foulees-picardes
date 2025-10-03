<?php

namespace App\Form;

use App\Dto\BulkEmailData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BulkEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('from', EmailType::class, [
                'label' => 'Expéditeur',
                'attr' => [
                    'placeholder' => 'communication@lesfouleespicardes.fr',
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'maxlength' => 120,
                ],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Contenu (Twig autorisé)',
                'attr' => [
                    'rows' => 14,
                    'placeholder' => "Bonjour {{ member.firstName }},\n\nMerci de votre participation...",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BulkEmailData::class,
        ]);
    }
}
