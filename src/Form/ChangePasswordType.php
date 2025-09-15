<?php

namespace App\Form;

use App\Dto\ChangePasswordData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email
            ->add('oldPassword', PasswordType::class, [
                'invalid_message' => 'L\'ancien mot de passe est incorrect',
                'label' => 'Mot de passe actuel',
                'toggle' => true,
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'label' => 'Nouveau mot de passe',
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'toggle' => true,
                ],
                'second_options' => [
                    'label' => 'Répéter le nouveau mot de passe',
                    'toggle' => true,
                ],
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Modifier',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangePasswordData::class,
            'csrf_protection' => true,
        ]);
    }
}
