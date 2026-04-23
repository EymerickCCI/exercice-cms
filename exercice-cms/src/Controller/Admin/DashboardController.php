<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\CommentaryRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private CommentaryRepository $commentaryRepository,
        private UserRepository $userRepository,
    ) {}

    public function index(): Response
    {
        $totalArticles     = $this->articleRepository->count([]);
        $publishedArticles = $this->articleRepository->count(['isPublished' => true]);
        $pendingComments   = $this->commentaryRepository->count(['isApprouved' => false]);
        $totalUsers        = $this->userRepository->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'total_articles'     => $totalArticles,
            'published_articles' => $publishedArticles,
            'pending_comments'   => $pendingComments,
            'total_users'        => $totalUsers,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<span style="color:#4fa3ff">Mon</span>CMS Admin')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Contenu');
        yield MenuItem::linkToRoute('Articles', 'fa fa-newspaper', 'admin_article_index');
        yield MenuItem::linkToRoute('Pages', 'fa fa-file-alt', 'admin_page_index');
        yield MenuItem::linkToRoute('Commentaires', 'fa fa-comments', 'admin_commentary_index');

        yield MenuItem::section('Taxonomie');
        yield MenuItem::linkToRoute('Catégories', 'fa fa-folder', 'admin_category_index');
        yield MenuItem::linkToRoute('Tags', 'fa fa-tags', 'admin_tag_index');

        yield MenuItem::section('Médias');
        yield MenuItem::linkToRoute('Galeries', 'fa fa-images', 'admin_gallery_index');
        yield MenuItem::linkToRoute('Images', 'fa fa-image', 'admin_image_index');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToRoute('Utilisateurs', 'fa fa-users', 'admin_user_index');

        yield MenuItem::section('');
        yield MenuItem::linkToUrl('← Retour au site', 'fa fa-arrow-left', '/');
    }
}
