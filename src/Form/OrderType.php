<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Order;
use App\Entity\Client;
use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('products', EntityType::class, array(
                'class'     => Product::class,
                'expanded'  => true,
                'multiple'  => true,
                'query_builder' => function(ProductRepository $pr) {
                    return $pr->createQueryBuilder('nd')->where('nd.isDeleted = false');
                },
            ))
            ->add('client', EntityType::class, array(
                'class'     => Client::class,
                'disabled'  => true,
            ))
            ->add('save', SubmitType::class, ['label' => 'Save'])
        ; 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class
        ));
    }
}