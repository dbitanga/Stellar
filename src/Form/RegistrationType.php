<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Store sign-up. Everything a customer needs to check out is required here,
 * with the midterm's password rules (8+ characters, a lowercase letter and a number).
 */
class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('lastName', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('username', TextType::class)
            ->add('email', EmailType::class, ['constraints' => [new NotBlank(), new Email()]])
            ->add('mobileNumber', TelType::class, ['constraints' => [
                new NotBlank(),
                new Regex(pattern: '/^\+?[\d\s-]{7,20}$/', message: 'Enter a valid mobile number.'),
            ]])
            ->add('deliveryAddress', TextareaType::class, ['constraints' => [new NotBlank()]])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'The passwords do not match.',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm password'],
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 8, max: 4096),
                    new Regex(pattern: '/[a-z]/', message: 'Password must contain at least one lowercase letter.'),
                    new Regex(pattern: '/\d/', message: 'Password must contain at least one number.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
