<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordConstraints = [new Length(min: 8, max: 4096)];
        if ($options['require_password']) {
            $passwordConstraints[] = new NotBlank();
        }

        $builder
            ->add('username', TextType::class)
            ->add('firstName', TextType::class, ['required' => false])
            ->add('middleName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('email', EmailType::class, ['required' => false])
            ->add('role', ChoiceType::class, [
                'choices' => User::STAFF_ROLE_LABELS,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'required' => $options['require_password'],
                'constraints' => $passwordConstraints,
                'help' => 'At least 8 characters. When editing, leave blank to keep the current password.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => true,
        ]);
        $resolver->setAllowedTypes('require_password', 'bool');
    }
}
