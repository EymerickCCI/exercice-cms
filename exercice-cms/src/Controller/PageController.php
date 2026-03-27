<?php

namespace App\Controller;

    use App\Entity\Page;
    use App\Repository\PageRepository;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    final class PageController extends AbstractController
    {
        #[Route('/page', name: 'app_page_index')]
        public function index(PageRepository $pageRepository): Response
        {
            $pages = $pageRepository->findBy(['isPublished' => true]);

            return $this->render('page/index.html.twig', [
                'pages' => $pages,
            ]);
        }

        #[Route('/page/{slug}', name: 'app_page_show')]
        public function show(string $slug, PageRepository $pageRepository): Response
        {
            $page = $pageRepository->findOneBy(['slug' => $slug]);

            if (!$page || !$page->isPublished()) {
                throw $this->createNotFoundException('Page non trouvée');
            }

            return $this->render('page/show.html.twig', [
                'page' => $page,
            ]);
        }

    }
