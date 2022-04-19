<?php

namespace App\Form;

use App\Entity\Category\Category;
use App\Entity\Competition\Competition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("name", TextType::class)
            ->add("slug", TextType::class)
            ->add("category", EntityType::class, [
                "class" => Category::class])
            ->add("matchesAgainst", IntegerType::class);
    }


    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            "data_class" => Competition::class,
        ]);
    }
}

