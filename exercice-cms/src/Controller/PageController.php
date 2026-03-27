<?php

namespace App\Controller;

use App\Entity\Page;
use App\Form\PageType;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/page')]
final class PageController extends AbstractController
{

    #[Route('', name: 'app_page_index', methods: ['GET'])]
    public function index(PageRepository $pageRepository): Response
    {
        $pages = $pageRepository->findBy(['isPublished' => true]);

        return $this->render('page/index.html.twig', [
            'pages' => $pages,
        ]);
    }


    #[Route('/new', name: 'app_page_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_WRITER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = new Page();
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->setCreatedAt(new \DateTimeImmutable());
            $page->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($page);
            $entityManager->flush();

            $this->addFlash('success', 'Page créée avec succès !');
            return $this->redirectToRoute('app_page_show', ['slug' => $page->getSlug()]);
        }

        return $this->render('page/new.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }


    #[Route('/{slug}', name: 'app_page_show', methods: ['GET'])]
    public function show(string $slug, PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if (!$page) {
            throw $this->createNotFoundException('Page non trouvée');
        }

        if (!$page->isPublished() && !$this->isGranted('ROLE_WRITER')) {
            throw $this->createNotFoundException('Page non trouvée');
        }

        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_page_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_WRITER')]
    public function edit(Request $request, Page $page, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Page mise à jour !');
            return $this->redirectToRoute('app_page_show', ['slug' => $page->getSlug()]);
        }

        return $this->render('page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_page_delete', methods: ['POST'])]
    #[IsGranted('ROLE_WRITER')]
    public function delete(Request $request, Page $page, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($page);
            $entityManager->flush();
            $this->addFlash('success', 'Page supprimée.');
        }

        return $this->redirectToRoute('app_page_index');
    }
}

