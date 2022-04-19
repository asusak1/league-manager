<?php

namespace App\Form;

use App\Entity\Competitor\Competitor;
use App\Entity\Sport\Sport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitorType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("name", TextType::class)
            ->add("slug", TextType::class)
            ->add("sport", EntityType::class, [
                "class" => Sport::class])
            ->add($builder->create(
                "country",
                CountryType::class,
                array("by_reference" => false)));
    }


    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            "data_class" => Competitor::class,
        ]);
    }
}

