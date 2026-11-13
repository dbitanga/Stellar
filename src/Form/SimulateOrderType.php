<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

/** Fake checkout used to create orders from the admin panel. One quantity field per product: qty_<id>. */
class SimulateOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', EntityType::class, [
                'class' => User::class,
                'query_builder' => static fn (EntityRepository $repo) => $repo->createQueryBuilder('u')
                    ->andWhere('u.role = :role')->setParameter('role', User::ROLE_USER)
                    ->andWhere('u.active = true')
                    ->orderBy('u.username', 'ASC'),
                'choice_label' => static fn (User $u): string => $u->getFullName() ? sprintf('%s (%s)', $u->getFullName(), $u->getUsername()) : (string) $u->getUsername(),
                'placeholder' => 'Choose a customer',
                'constraints' => [new NotNull(message: 'Please choose a customer.')],
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => ['Cash on delivery' => Order::PAYMENT_COD, 'Card' => Order::PAYMENT_CARD],
                'expanded' => true,
                'data' => Order::PAYMENT_COD,
            ])
            ->add('returnReason', ChoiceType::class, [
                'label' => 'Return after delivery',
                'choices' => array_combine(Order::RETURN_REASONS, Order::RETURN_REASONS),
                'required' => false,
                'placeholder' => 'No, the customer keeps the order',
                'help' => 'Pick a reason to have the customer request a return once the order is delivered.',
            ]);

        /** @var Product $product */
        foreach ($options['products'] as $product) {
            $builder->add('qty_'.$product->getId(), IntegerType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => '0',
                'attr' => ['min' => 0, 'max' => $product->getStock(), 'class' => 'form-control-sm'],
                'constraints' => [new Range(min: 0, max: 99)],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['products' => []]);
        $resolver->setAllowedTypes('products', 'array');
    }
}
