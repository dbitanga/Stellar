<?php

namespace App\Form;

use App\Entity\Product;
use App\Enum\ProductCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('category', EnumType::class, [
                'class' => ProductCategory::class,
                'choice_label' => fn (ProductCategory $category) => $category->label(),
                'placeholder' => 'Choose a category',
            ])
            ->add('tier', ChoiceType::class, [
                'choices' => array_combine(Product::TIERS, Product::TIERS),
                'placeholder' => 'Choose a tier',
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price (₱)',
                'scale' => 2,
                'html5' => true,
                'input' => 'string',
                'attr' => ['step' => '0.01', 'min' => 0],
            ])
            ->add('stock', IntegerType::class, ['attr' => ['min' => 0]])
            ->add('processor', TextType::class)
            ->add('graphics', TextType::class)
            ->add('ram', TextType::class, ['label' => 'Memory'])
            ->add('storage', TextType::class)
            ->add('imagePath', TextType::class, [
                'label' => 'Image path',
                'attr' => ['placeholder' => 'images/products/filename.png'],
            ])
            ->add('visible', CheckboxType::class, [
                'label' => 'Visible on the website',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }
}