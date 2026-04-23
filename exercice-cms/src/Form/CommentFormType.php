<?php

namespace App\Form;

use App\Entity\Commentary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameAuthor', TextType::class, [
                'label'       => 'Votre nom',
                'constraints' => [
                    new NotBlank(['message' => 'Votre nom est requis.']),
                    new Length(['max' => 255]),
                ],
                'attr' => ['placeholder' => 'Jean Dupont'],
            ])
            ->add('emailAuthor', EmailType::class, [
                'label'    => 'Votre email (non publié)',
                'required' => false,
                'constraints' => [
                    new Email(['message' => 'Email invalide.']),
                ],
                'attr' => ['placeholder' => 'email@exemple.fr'],
            ])
            ->add('content', TextareaType::class, [
                'label'       => 'Votre commentaire',
                'constraints' => [
                    new NotBlank(['message' => 'Le commentaire ne peut pas être vide.']),
                    new Length(['min' => 5, 'minMessage' => 'Trop court (min 5 caractères).']),
                ],
                'attr' => ['rows' => 4, 'placeholder' => 'Écrivez votre commentaire…'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentary::class,
        ]);
    }
}
