<?php

namespace App\Form;

use App\Dto\LdapUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LdapUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cn', TextType::class, [
                'help' => 'L\'identifiant de l\'utilisateur : 1 chaîne de caractères, en minuscule - Exemple : jdubois',
                'attr' => [
                    'readonly' => $options['edit']
                ]
            ])
            ->add('displayedName', TextType::class, [
                'help' => 'Le nom complet de l\'utilisateur affiché - Exemple : Jean DUBOIS'
            ])
            ->add('email', EmailType::class, [
                'help' => 'L\'adresse e-mail de l\'utilisateur - Exemple : jdubois@gmail.com'
            ])
            ->add('phone', TextType::class, [
                'help' => 'Le numéro de téléphone au format français - Exemple : 0601020304'
            ])
            ->add('password', PasswordType::class, [
                'help' => 'Le mot de passe doit contenir au moins : 10 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LdapUser::class,
            'translation_domain' => 'app',
            'edit' => false
        ]);
    }
}
