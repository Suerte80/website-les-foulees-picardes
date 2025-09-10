<?php

namespace App\Form;

use App\Dto\RegistrationData;
use App\Entity\MembershipRequest;
use PHPUnit\Framework\Constraint\IsTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\Regex;

class MembershipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'Les adresses mails doivent correspondre.',
                'required' => true,
                'first_options' => [
                    'label' => 'Adresse email',
                    'attr' => [
                        'maxlength' => 255,
                        'minlength' => 4,
                    ],
                ],
                'second_options' => [
                    'label' => 'Répéter l\'adresse email',
                    'attr' => [
                        'maxlength' => 255,
                        'minlength' => 4,
                    ],
                ],
            ])

            // Champs mot de passe
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'toggle' => true,
                ],
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                    'toggle' => true,
                ],
                'required' => true,
            ])

            // Champ prénom
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,

                'attr' => [
                    'maxlength' => 100,
                    'minlength' => 1,
                ],
            ])

            // Champ nom
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => true,

                'attr' => [
                    'maxlength' => 100,
                    'minlength' => 1,
                ],
            ])

            // Champ adresse
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => true,

                'attr' => [
                    'maxlength' => 255,
                    'minlength' => 1,
                ],
            ])

            // Champ date de naissance
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date de naissance',
                'required' => true,

                'attr' => [
                    'format' => 'dd-MM-yyyy',
                ],
            ])

            // Champ numéro de téléphone
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => true,

                'attr' => [
                    'minlength' => 8,
                    'maxlength' => 18
                ],
            ])

            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => false,
            ])

            ->add('rgpdAccepted', CheckboxType::class, [
                'label' => 'Acceptez le rgpd',
                'required' => true,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationData::class,
        ]);
    }
}
