<?php

namespace App\Form\Auth;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'register.form.email'])
            ->add('firstName', TextType::class, ['label' => 'register.form.firstname', 'required' => true])
            ->add('lastName', TextType::class, ['label' => 'register.form.lastname', 'required' => false])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // never goes onto the entity directly
                'first_options' => ['label' => 'register.form.password', 'attr' => ['autocomplete' => 'new-password']],
                'second_options' => ['label' => 'register.form.repeat_password', 'attr' => ['autocomplete' => 'new-password']],
                'invalid_message' => 'register.errors.password_mismatch',
                'constraints' => [
                    new NotBlank(message: 'register.errors.password_blank'),
                    new Length(min: 8, minMessage: 'register.errors.password_short', max: 4096),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}