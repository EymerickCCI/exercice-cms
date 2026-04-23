<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Doctrine\ORM\EntityManagerInterface;

#[AdminCrud(routePath: '/image', routeName: 'image')]
class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image')
            ->setEntityLabelInPlural('Images')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['caption', 'filename'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Ajouter une image'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->setIcon('fa fa-pencil')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setIcon('fa fa-trash')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $a) => $a->setIcon('fa fa-eye')->setLabel(''));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('gallery', 'Galerie'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield ImageField::new('filename', 'Image')
            ->setBasePath('/uploads/gallery')
            ->setUploadDir('public/uploads/gallery')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(true);

        yield TextField::new('caption', 'Légende')->setRequired(false);

        yield AssociationField::new('gallery', 'Galerie');

        yield DateTimeField::new('createdAt', 'Ajoutée le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ($entityInstance instanceof Image) {
            $entityInstance->setCreatedAt(new \DateTimeImmutable());
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
}
