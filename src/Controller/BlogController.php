<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Repository\ArticleRepository;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function blog(ArticleRepository $articleRepository)
    {

        /*$repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findAll();*/
        
        $articles = $articleRepository->findAll();
        
        dump($articles);

        return $this->render('blog/blog.html.twig', [
            'pageTitle' => 'Blog',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/blog/article/{id}", name="articleGet", requirements={"id"="\d+"})
     */
    public function article($id, ArticleRepository $articleRepository)
    {   

        /*switch ($id) {
            case 1:
                $title = 'Model-View-Controller';
                break;
            case 2:
                $title = 'Объектно-ориентированное программирование';
                break;
            case 3:
                $title = 'Bootstrap';
                break;
            case 4:
                $title = 'Bootstrap (фреймворк)';
                break;
            case 5:
                $title = 'HTML';
                break;
            default:
                //redirect 404
                throw $this->createNotFoundException('Article not found!');
        }*/

        $article = $articleRepository->findOneBy(['id' => $id]);
dump($article);
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
        dump($previousArticle);
        $nextArticle = $articleRepository->findPrevNextArticles($article, 'next');
        dump($nextArticle);

        return $this->render("blog/article.html.twig", [
            'article' => $article,
            'previousPage' => $previousArticle?$previousArticle->getId():false,
            'nextPage' => $nextArticle?$nextArticle->getId():false,
        ]);
    }
}
