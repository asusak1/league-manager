<?php

namespace App\Form;

use App\Entity\Competition\Competition;
use App\Entity\Season\Season;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeasonType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("name", TextType::class)
            ->add("startDate", DateTimeType::class,
                ["html5" => false, "widget" => "single_text", "format" => "yyyy-MM-dd HH:mm:ss"])
            ->add("endDate", DateTimeType::class,
                ["html5" => false, "widget" => "single_text", "format" => "yyyy-MM-dd HH:mm:ss"])
            ->add("competition", EntityType::class, [
                "class" => Competition::class]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            "data_class" => Season::class,
        ]);
    }
}

