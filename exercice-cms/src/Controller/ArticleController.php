<?php
 
namespace App\Controller;
 
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
 
#[Route('/blog')]
final class ArticleController extends AbstractController
{
    public function __construct(
        private string $articlesDirectory,
    ) {}
 
    #[Route('', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        $categorySlug = $request->query->get('category');
        $tagId        = $request->query->get('tag');
        $search       = $request->query->get('q');
 
        // On ne montre que les articles publiés aux visiteurs
        $qb = $articleRepository->createQueryBuilder('a')
            ->join('a.category', 'c')
            ->addSelect('c')
            ->where('a.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('a.createdAt', 'DESC');
 
        if ($categorySlug) {
            $qb->andWhere('c.slug = :cat')->setParameter('cat', $categorySlug);
        }
 
        if ($tagId) {
            $qb->join('a.tags', 't')
               ->andWhere('t.id = :tagId')
               ->setParameter('tagId', $tagId);
        }
 
        if ($search) {
            $qb->andWhere('a.title LIKE :q OR a.content LIKE :q OR a.metaDescription LIKE :q')
               ->setParameter('q', '%' . $search . '%');
        }
 
        $articles   = $qb->getQuery()->getResult();
        $categories = $categoryRepository->findAll();
 
        return $this->render('article/index.html.twig', [
            'articles'        => $articles,
            'categories'      => $categories,
            'current_category' => $categorySlug,
            'search'          => $search,
        ]);
    }
 
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_WRITER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form    = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('featuredImage')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->articlesDirectory, $filename);
                $article->setFeaturedImage($filename);
            }
 
            $article->setAuthor($this->getUser());
            $article->setCreatedAt(new \DateTimeImmutable());
            $article->setUpdatedAt(new \DateTimeImmutable());
 
            $entityManager->persist($article);
            $entityManager->flush();
 
            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }
 
        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form'    => $form,
        ]);
    }
 
    #[Route('/{id}', name: 'app_article_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Article $article): Response
    {
        // Accès refusé si brouillon et non admin/rédacteur
        if (!$article->isPublished() && !$this->isGranted('ROLE_WRITER')) {
            throw $this->createNotFoundException('Article non disponible.');
        }
 
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
 
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_WRITER')]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('featuredImage')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->articlesDirectory, $filename);
                $article->setFeaturedImage($filename);
            }
 
            $article->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
 
            $this->addFlash('success', 'Article mis à jour !');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }
 
        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form'    => $form,
        ]);
    }
 
    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    #[IsGranted('ROLE_WRITER')]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article supprimé.');
        }
 
        return $this->redirectToRoute('app_article_index');
    }
}