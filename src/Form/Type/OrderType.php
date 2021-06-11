<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotNull;

use Entity\Order;

class OrderType extends AbstractType 
{

    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
            ->add('total', TextType::class, [
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add('discount', TextType::class,)
            ->add('items', TextType::class, [
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add('shipping_details', TextType::class, [
                'constraints' => [
                    new NotNull(),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) 
    {
        $resolver->setDefaults([
           'data' => Order::class, 
        ]);
    }
}

