<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\ArticleRepository;
use App\Repository\GalleryRepository;
use App\Repository\ImageRepository;
use App\Repository\CategoryRepository;

final class HomeController extends AbstractController
{
    #[Route('', name: 'app_home')]
    #[Route('/home')]
    public function index(
        ArticleRepository $articleRepo,
        GalleryRepository $galerieRepo,
        ImageRepository $imageRepo,
        CategoryRepository $categoryRepo
        ): Response {

        // 🔹 Derniers articles
        $latestArticles = $articleRepo->findBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC'],
            6
        );

        // 🔹 Dernières galeries
        $latestGalleries = $galerieRepo->findBy(
            [],
            ['createdAt' => 'DESC'],
            4
        );

        // 🔹 Stats
        $stats = [
            'articles' => $articleRepo->count(['isPublished' => true]),
            'galleries' => $galerieRepo->count([]),
            'images' => $imageRepo->count([]),
            'categories' => $categoryRepo->count([]),
        ];

        return $this->render('home/index.html.twig', [
            'latest_articles' => $latestArticles,
            'latest_galleries' => $latestGalleries,
            'stats' => $stats,
        ]);
    }
}
