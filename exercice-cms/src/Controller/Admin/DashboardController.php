<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\CommentaryRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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
        $totalArticles = $this->articleRepository->count([]);
        $publishedArticles = $this->articleRepository->count(['isPublished' => true]);
        $pendingComments = $this->commentaryRepository->count(['isApprouved' => false]);
        $totalUsers = $this->userRepository->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'total_articles' => $totalArticles,
            'published_articles' => $publishedArticles,
            'pending_comments' => $pendingComments,
            'total_users' => $totalUsers,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<span style="color:#4fa3ff">Mon</span>CMS Admin')
            ->renderContentMaximized();
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addJsFile('https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js')
            ->addHtmlContentToBody(<<<'HTML'
                <script>
                function initCKEditors() {
                    ['Article_content', 'Page_content'].forEach(function (id) {
                        var element = document.getElementById(id);
                        if (element && typeof CKEDITOR !== 'undefined' && !CKEDITOR.instances[id]) {
                            CKEDITOR.replace(id, {
                                height: 300,
                                uiColor: '#ffffff',
                                toolbar: 'Full'
                            });
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', initCKEditors);
                </script>
                HTML);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Contenu');
        yield MenuItem::linkToRoute('Articles', 'fa fa-newspaper', 'admin_article_index');
        yield MenuItem::linkToRoute('Pages', 'fa fa-file-alt', 'admin_page_index');
        yield MenuItem::linkToRoute('Commentaires', 'fa fa-comments', 'admin_commentary_index');

        yield MenuItem::section('Taxonomie');
        yield MenuItem::linkToRoute('Categories', 'fa fa-folder', 'admin_category_index');
        yield MenuItem::linkToRoute('Tags', 'fa fa-tags', 'admin_tag_index');

        yield MenuItem::section('Medias');
        yield MenuItem::linkToRoute('Galeries', 'fa fa-images', 'admin_gallery_index');
        yield MenuItem::linkToRoute('Images', 'fa fa-image', 'admin_image_index');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToRoute('Utilisateurs', 'fa fa-users', 'admin_user_index');

        yield MenuItem::section('');
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');
    }
}
