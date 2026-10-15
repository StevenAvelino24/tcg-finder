<?php

namespace App\Form\Auth;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // never goes onto the entity directly
                'first_options' => ['label' => 'forgot_password.form.password', 'attr' => ['autocomplete' => 'new-password']],
                'second_options' => ['label' => 'forgot_password.form.repeat_password', 'attr' => ['autocomplete' => 'new-password']],
                'invalid_message' => 'forgot_password.errors.password_mismatch',
                'constraints' => [
                    new NotBlank(message: 'forgot_password.errors.password_blank'),
                    new Length(min: 8, minMessage: 'forgot_password.errors.password_short', max: 4096),
                ],
            ]);
    }
}