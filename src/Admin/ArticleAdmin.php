<?php
namespace App\Admin;

use App\Entity\Category;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use FOS\CKEditorBundle\Form\Type\CKEditorType;

final class ArticleAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Текст статьи',
                //'attr' => ['rows' => '10']
            ])
            ->add('author', null, [
                'label' => 'Автор',
            ])
            ->add('category', EntityType::class, [
                'label' => 'Категория',
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('datetime')
            ->add('author')
            ->add('category', null, [], EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->addIdentifier('title', null, [
                'label' => 'Заголовок',
            ])
            ->add('datetime', null, [
                'format' => 'Y-m-d H:i:s',
                'label' => 'Дата',
            ])
            ->add('author', null, [
                'label' => 'Автор',
            ])
            ->add('category.name', null, [
                'label' => 'Категория',
            ])
        ;
    }

    public function toString($object)
    {
        return $object instanceof Article
            ? $object->getTitle()
            : 'Статья'; // shown in the breadcrumb on the create view
    }

    public function preValidate($article)
    {   
        // set current datetime for new and updated article
        $article->setDatetime(new \DateTime('now'));
    }
}