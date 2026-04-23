<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Doctrine\ORM\EntityManagerInterface;

#[AdminCrud(routePath: '/user', routeName: 'user')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['email', 'firstname', 'lastname']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Nouvel utilisateur'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setLabel('Supprimer'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('email', 'Email'))
            ->add(ChoiceFilter::new('roles', 'Rôle')->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Rédacteur'      => 'ROLE_WRITER',
                'Utilisateur'    => 'ROLE_USER',
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('firstname', 'Prénom');
        yield TextField::new('lastname', 'Nom');
        yield EmailField::new('email', 'Email');

        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Rédacteur'      => 'ROLE_WRITER',
                'Utilisateur'    => 'ROLE_USER',
            ])
            ->allowMultipleChoices()
            ->renderAsBadges([
                'ROLE_ADMIN'  => 'danger',
                'ROLE_WRITER' => 'warning',
                'ROLE_USER'   => 'info',
            ]);

        yield TextField::new('password', 'Mot de passe')
        ->setFormType(PasswordType::class)
        ->setRequired($pageName === Crud::PAGE_NEW)
        ->onlyOnForms()
        ->setFormTypeOptions([
            'empty_data' => '',
        ])
        ->setHelp('Laissez vide pour ne pas modifier le mot de passe');

        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
        yield DateTimeField::new('updatedAt', 'Modifié le')->hideOnForm();
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $plainPassword = $entityInstance->getPassword();

            // Si le champ password est vide, on restaure l'ancien hash
            if (empty($plainPassword)) {
                $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
                $entityInstance->setPassword($originalData['password']);
            } else {
                // Sinon on hash le nouveau mot de passe
                if (!str_starts_with($plainPassword, '$2y$') && !str_starts_with($plainPassword, '$argon')) {
                    $hashed = $this->passwordHasher->hashPassword($entityInstance, $plainPassword);
                    $entityInstance->setPassword($hashed);
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPassword(User $user): void
    {
        $plainPassword = $user->getPassword();
        if ($plainPassword) {
            $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashed);
        }
    }
}
