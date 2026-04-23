<?php

namespace App\Controller\Admin;

use App\Entity\Gallery;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Doctrine\ORM\EntityManagerInterface;

#[AdminCrud(routePath: '/gallery', routeName: 'gallery')]
class GalleryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Gallery::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Galerie')
            ->setEntityLabelInPlural('Galeries')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['title', 'description'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Nouvelle galerie'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->setIcon('fa fa-pencil')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setIcon('fa fa-trash')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $a) => $a->setIcon('fa fa-eye')->setLabel(''));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('title', 'Titre'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('title', 'Titre');

        yield TextareaField::new('description', 'Description')
            ->setRequired(false)
            ->hideOnIndex();

        yield AssociationField::new('images', 'Images')
            ->hideOnForm()
            ->setHelp('Gérez les images depuis le menu "Images"');

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ($entityInstance instanceof Gallery) {
            $entityInstance->setCreatedAt(new \DateTimeImmutable());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ($entityInstance instanceof Gallery) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}
