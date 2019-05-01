<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\ArticleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @Route("/article")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     * @param Request $request input data: page number, orderBy, direction of order, (limit articles per page)
     */
    public function blog(ArticleRepository $articleRepository, PaginatorInterface $paginator, Request $request)
    {
        //$paginator  = $this->get('knp_paginator');//not work in symfony 4
        
        $query = $articleRepository->createQueryBuilder('a')->select('a')->leftJoin('a.user', 'aUser')->getQuery();

        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Define the page parameter
            5, // articles per page
            [
                'defaultSortFieldName' => 'a.updatedAt', 
                'defaultSortDirection' => 'desc'
            ],
        );
        
        return $this->render('blog/blog.html.twig', [
            'pageTitle' => 'Blog',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/{id}", name="article_view", requirements={"id"="\d+"})
     */
    public function articleView($id, ArticleRepository $articleRepository, Request $request)
    {   
        $article = $articleRepository->findOneBy(['id' => $id]);
        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }

        $previousArticle = $articleRepository->findPrevNextArticles($article, 'prev');
        $nextArticle = $articleRepository->findPrevNextArticles($article, 'next');

        return $this->render("blog/articleShow.html.twig", [
            'article' => $article,
            'request' => $request,
            'previousPage' => $previousArticle?$previousArticle->getId():false,
            'nextPage' => $nextArticle?$nextArticle->getId():false,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="article_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function articleEdit($id, Request $request, Article $article, ArticleRepository $articleRepository)
    {   
        $article = $articleRepository->findOneBy(['id' => $id]);

        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('article_view', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('blog/articleEdit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'title' => 'Редактирование статьи',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="article_delete", requirements={"id"="\d+"})
     */
    public function articleDelete($id, ArticleRepository $articleRepository, Request $request)
    {   
        $article = $articleRepository->findOneBy(['id' => $id]);

        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }
        
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
            return $this->redirectToRoute('blog');
        }

        return $this->render("blog/articleDelete.html.twig", [
            'title' => 'Удаление статьи',
            'article' => $article,
            'deleted' => false,
        ]);
    }

    /**
     * @Route("/new", name="article_new", methods={"GET","POST"})
     */
    public function articleNew(Request $request)
    {   
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $article->setUser($this->getUser());
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('blog');
        }
    
        return $this->render('blog/articleNew.html.twig', [
            'form' => $form->createView(),
            'title' => 'Создание новой статьи',
        ]);
    }
}
