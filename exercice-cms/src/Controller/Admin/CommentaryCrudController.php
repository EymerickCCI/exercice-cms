<?php

namespace App\Controller\Admin;

use App\Entity\Commentary;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[AdminCrud(routePath: '/commentary', routeName: 'commentary')]
class CommentaryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commentary::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commentaire')
            ->setEntityLabelInPlural('Commentaires')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['nameAuthor', 'emailAuthor', 'content'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        // Action rapide : approuver
        $approve = Action::new('approve', 'Approuver', 'fa fa-check')
            ->displayIf(fn(Commentary $c) => !$c->isApprouved())
            ->linkToCrudAction('approveComment')
            ->addCssClass('btn btn-success btn-sm');

        // Action rapide : rejeter
        $reject = Action::new('reject', 'Rejeter', 'fa fa-times')
            ->displayIf(fn(Commentary $c) => $c->isApprouved())
            ->linkToCrudAction('rejectComment')
            ->addCssClass('btn btn-warning btn-sm');

        return $actions
            ->add(Crud::PAGE_INDEX, $approve)
            ->add(Crud::PAGE_INDEX, $reject)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->setIcon('fa fa-pencil')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setIcon('fa fa-trash')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $a) => $a->setIcon('fa fa-eye')->setLabel(''));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isApprouved', 'Approuvé'))
            ->add(TextFilter::new('nameAuthor', 'Auteur'))
            ->add(EntityFilter::new('article', 'Article'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('nameAuthor', 'Auteur');
        yield EmailField::new('emailAuthor', 'Email')->hideOnIndex();

        yield TextareaField::new('content', 'Contenu')
            ->setNumOfRows(4);

        yield AssociationField::new('article', 'Article');

        yield BooleanField::new('isApprouved', 'Approuvé')
            ->renderAsSwitch(true);

        yield DateTimeField::new('createdAt', 'Date')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function approveComment(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $id = $this->getContext()->getRequest()->query->getInt('entityId');
        $comment = $em->getRepository(Commentary::class)->find($id);
        if ($comment) {
            $comment->setIsApprouved(true);
            $em->flush();
            $this->addFlash('success', 'Commentaire approuvé.');
        }

        return $this->redirect($adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    public function rejectComment(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $id = $this->getContext()->getRequest()->query->getInt('entityId');
        $comment = $em->getRepository(Commentary::class)->find($id);
        if ($comment) {
            $comment->setIsApprouved(false);
            $em->flush();
            $this->addFlash('warning', 'Commentaire rejeté.');
        }

        return $this->redirect($adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
