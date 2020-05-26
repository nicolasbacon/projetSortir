<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class, array(
                'class' => Campus::class,
                'choice_label' => 'nom'))
            ->add('research', TextType::class, array(
                'label' => 'Le nom de la sortie contient : ',
                'required' => false
            ))
            ->add('dateDebut', DateType::class, array(
                'label' => 'Entre :',
                'widget' => 'single_text',
                'required' => false
            ))
            ->add('dateFin', DateType::class, array(
                'label' => 'et ',
                'widget' => 'single_text',
                'required' => false
            ))
            ->add('organisateur', CheckboxType::class, array(
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false
            ))
            ->add('inscrit', CheckboxType::class, array(
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false
            ))
            ->add('nonInscrit', CheckboxType::class, array(
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false
            ))
            ->add('sortiePasse', CheckboxType::class, array(
                'label' => 'Sorties passées',
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
