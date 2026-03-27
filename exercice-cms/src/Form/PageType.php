<?php

namespace App\Form;

use App\Entity\Page;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('slug', null, [
                'label' => 'Slug URL',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => '8'],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Métadescription (SEO)',
                'required' => false,
                'attr' => ['rows' => '3'],
            ])
            ->add('isPublished', null, [
                'label' => 'Publier la page',
            ])
            ->add('parent', EntityType::class, [
                'class' => Page::class,
                'choice_label' => 'title',
                'required' => false,
                'label' => 'Page parente (pour hiérarchie)',
                'placeholder' => '-- Aucune (page principale)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
