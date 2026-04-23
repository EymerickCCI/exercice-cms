<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Doctrine\ORM\EntityManagerInterface;

#[AdminCrud(routePath: '/article', routeName: 'article')]
class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['title', 'content', 'metaDescription'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Nouvel article'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->setIcon('fa fa-pencil')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setIcon('fa fa-trash')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $a) => $a->setIcon('fa fa-eye')->setLabel(''));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('title', 'Titre'))
            ->add(BooleanFilter::new('isPublished', 'Publié'))
            ->add(EntityFilter::new('category', 'Catégorie'))
            ->add(EntityFilter::new('author', 'Auteur'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('title', 'Titre');

        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('title')
            ->hideOnIndex();

        yield TextareaField::new('content', 'Contenu')
            ->hideOnIndex()
            ->setNumOfRows(15);

        yield TextField::new('metaDescription', 'Méta-description')
            ->hideOnIndex()
            ->setHelp('155 caractères maximum pour le SEO');

        yield ImageField::new('featuredImage', 'Image mise en avant')
            ->setBasePath('/uploads/articles')
            ->setUploadDir('public/uploads/articles')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);

        yield BooleanField::new('isPublished', 'Publié')
            ->renderAsSwitch(true);

        yield AssociationField::new('category', 'Catégorie');
        yield AssociationField::new('author', 'Auteur')->hideOnForm();
        yield AssociationField::new('tags', 'Tags')
            ->setFormTypeOptions(['by_reference' => false])
            ->hideOnIndex();

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ($entityInstance instanceof Article) {
            $entityInstance->setAuthor($this->getUser());
            $entityInstance->setCreatedAt(new \DateTimeImmutable());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ($entityInstance instanceof Article) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}
