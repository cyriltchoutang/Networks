<?php

namespace App\Form;

use App\Entity\Vehicle;
use App\Entity\Owner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mark')
            ->add('model')
            ->add('registrationNumber')
            ->add('characteristics')
            ->add('registrationDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('owner', EntityType::class, [
                'class' => Owner::class,
                'choice_label' => 'lastname', // Assurez-vous d'avoir une méthode __toString() ou un champ 'name' dans votre entité Owner
                'placeholder' => 'Select an owner',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Register'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}
