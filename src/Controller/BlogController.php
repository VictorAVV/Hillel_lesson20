<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     * @param Request $request input data: page number, orderBy, direction of order, (limit articles per page)
     */
    public function blog(PaginatorInterface $paginator, ArticleRepository $articleRepository, Request $request)
    {
        //$paginator  = $this->get('knp_paginator');//not work in symfony 4
        
        $query = $articleRepository->createQueryBuilder('a')->select('a')->getQuery();

        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Define the page parameter
            5, // articles per page
            [
                'defaultSortFieldName' => 'a.datetime', 
                'defaultSortDirection' => 'desc'
            ],
        );
        
        return $this->render('blog/blog.html.twig', [
            'pageTitle' => 'Blog',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}", name="articleGet", requirements={"id"="\d+"})
     */
    public function article($id, ArticleRepository $articleRepository)
    {   
        $article = $articleRepository->findOneBy(['id' => $id]);
        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }

        $previousArticle = $articleRepository->findPrevNextArticles($article, 'prev');
        $nextArticle = $articleRepository->findPrevNextArticles($article, 'next');

        return $this->render("blog/article.html.twig", [
            'article' => $article,
            'previousPage' => $previousArticle?$previousArticle->getId():false,
            'nextPage' => $nextArticle?$nextArticle->getId():false,
        ]);
    }

    /**
     * @Route("/article-create", name="articleCreate")
     */
    public function articleSave(Request $request, ArticleRepository $articleRepository)
    {   
        // если передана статья через POST:
        if ($request->request->get('titleArticle')) {
            if (strlen($request->request->get('titleArticle')) == 0 || strlen($request->request->get('contentArticle')) == 0 ) {
                //нужно создать обычную html страницу с текстом об ошибке
                throw $this->createNotFoundException('Article must have title and content!');
            }
            if (strlen($request->request->get('titleArticle')) >250 ) {
                //нужно создать обычную html страницу с текстом об ошибке
                throw $this->createNotFoundException('Length of article title must be less then 250 characters!');
            }
            if (strlen($request->request->get('contentArticle')) > 100000 ) {
                //нужно создать обычную html страницу с текстом об ошибке
                throw $this->createNotFoundException('Content of article too big!');
            }
            if (strlen($request->request->get('authorArticle')) > 100000 ) {
                //нужно создать обычную html страницу с текстом об ошибке
                throw $this->createNotFoundException('Length of author\'s name must be less then 250 characters!');
            }

            //if POST contains id - update article. Else create new article
            $id = $request->request->get('id');
            if ($id) {
                $article = $articleRepository->findOneBy(['id' => $id]);
                if (null == $article) {
                    throw $this->createNotFoundException('Article not found!');
                }
            } else {
                $article = new Article();
            }

            $entityManager = $this->getDoctrine()->getManager();
            
            $article->setTitle($request->request->get('titleArticle'));
            $article->setContent($request->request->get('contentArticle'));
            $article->setAuthor($request->request->get('authorArticle'));
            $article->setDatetime(new \DateTime());
            if (!$id) {
                $entityManager->persist($article);
            }
            $entityManager->flush();
            
            return $this->redirectToRoute("articleGet", [
                'id' => $article->getId(),
            ]);
        }

        return $this->render("blog/articleEdit.html.twig", [
            'title' => 'Создание новой статьи',
            'article' => ['title' => '', 'content' => '', 'id' => '', 'author' => ''],
        ]);
    }

    /**
     * @Route("/article-edit/{id}", name="articleEdit", requirements={"id"="\d+"})
     */
    public function articleEdit($id, ArticleRepository $articleRepository)
    {   
        $article = $articleRepository->findOneBy(['id' => $id]);

        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }

        return $this->render("blog/articleEdit.html.twig", [
            'title' => 'Редактирование статьи',
            'article' => $article,
        ]);
    }

    /**
     * @Route("/article-delete/{id}", name="articleDelete", requirements={"id"="\d+"})
     */
    public function articleDelete($id, ArticleRepository $articleRepository, Request $request)
    {   
        $article = $articleRepository->findOneBy(['id' => $id]);

        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }
        
        $idFromPost = $request->request->get('id');
        $articleTitle = $article->getTitle();
        
        //if deletion is confirmed:
        if ($idFromPost && $request->request->get('confirmDelete')) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            return $this->render("blog/articleDelete.html.twig", [
                'title' => 'Статья удалена',
                'article' => ['title' => $articleTitle],
                'deleted' => true,
            ]);
        }
        
        return $this->render("blog/articleDelete.html.twig", [
            'title' => 'Удаление статьи',
            'article' => $article,
            'deleted' => false,
        ]);
    }


     /**
     * @Route("/new-article", name="articleNew")
     */
    public function articleNew(Request $request, ArticleRepository $articleRepository)
    {   

        $article = new Article();
        $article->setDatetime(new \DateTime('now'));

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class, ['label' => 'Название статьи:'])
            ->add('content', TextType::class, ['label' => 'Текст статьи:'])
            ->add('author', TextType::class, ['label' => 'Автор:'])
            ->add('save', SubmitType::class, ['label' => 'Сохранить'])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $article = $form->getData();
    
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();
    
            //return $this->redirectToRoute('blog');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            
            return $this->redirectToRoute("articleGet", [
                'id' => $article->getId(),
            ]);
        }
    
        return $this->render('blog/articleNew.html.twig', [
            'form' => $form->createView(),
            'title' => 'Создание новой статьи',
        ]);
    }
}
