<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog/{orderBy}/{directionOrder}", name="blog")
     */
    public function blog(ArticleRepository $articleRepository, $orderBy = 'datetime', $directionOrder = 'asc', $offset = 0)
    {
        $directionOrder = \strtolower(trim($directionOrder));
        if ('desc' != $directionOrder)
        {
            $directionOrder = 'asc';
        }
        
        $orderBy = \strtolower(trim($orderBy));
        if (!in_array($orderBy, ['title', 'author', 'datetime'])){
            $orderBy = 'datetime';
        }

        $articles = $articleRepository->findBy(array(), [$orderBy => $directionOrder]);
        
        //dump($orderBy);
        //dump($directionOrder);
        dump($articles);

        return $this->render('blog/blog.html.twig', [
            'pageTitle' => 'Blog',
            'articles' => $articles,
            'orderBy' => $orderBy,
            'directionOrder' => $directionOrder,
            'offset' => $offset,
        ]);
    }

    /**
     * @Route("/article/{id}", name="articleGet", requirements={"id"="\d+"})
     */
    public function article($id, ArticleRepository $articleRepository)
    {   

        $article = $articleRepository->findOneBy(['id' => $id]);
//dump($article);
        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }

        if ($id) {
            $previousPage = $id - 1;
            $nextPage = $id + 1;
        }
        if ($id == 1) {
            $previousPage = false;
        }
        if ($id == 5) {
            $nextPage = false;
        }

        $previousArticle = $articleRepository->findPrevNextArticles($article, 'prev');
//        dump($previousArticle);
        $nextArticle = $articleRepository->findPrevNextArticles($article, 'next');
//        dump($nextArticle);

        return $this->render("blog/article.html.twig", [
            'article' => $article,
            'previousPage' => $previousArticle?$previousArticle->getId():false,
            'nextPage' => $nextArticle?$nextArticle->getId():false,
        ]);
    }

    /**
     * @Route("/article-create/", name="articleCreate")
     */
    public function articleSave(Request $request, ArticleRepository $articleRepository)
    {   
        // если передана статья через POST
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
                throw $this->createNotFoundException('Lenght of author\'s name must be less then 250 characters!');
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
            if (!$request->request->get('id')) {
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
    public function articleEdit($id, ArticleRepository $articleRepository)
    {   
        $article = $articleRepository->findOneBy(['id' => $id]);

        if (null == $article) {
            throw $this->createNotFoundException('Article not found!');
        }

        return $this->render("blog/articleDelete.html.twig", [
            'title' => 'Редактирование статьи',
            'article' => $article,
        ]);
    }
}
