<?php

namespace App\Form;

use App\Entity\Country\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("ISO", TextType::class);
    }


    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            "data_class" => Country::class
        ]);
    }
}


