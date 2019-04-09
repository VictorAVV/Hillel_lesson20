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

final class ArticleAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Статья')
                ->with('')
                    ->add('title', TextType::class)
                    ->add('content', TextareaType::class)
                    ->add('datetime')
                    ->add('author')
                    ->add('category', EntityType::class, [
                        'class' => Category::class,
                        'choice_label' => 'name',
                    ])
                ->end()
            ->end()
            ->tab('Empty tab for additional options')
                // ...
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('datetime')
            ->add('author')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('title')
            ->addIdentifier('datetime')
            ->addIdentifier('author')
        ;
    }

    public function toString($object)
    {
        return $object instanceof Article
            ? $object->getTitle()
            : 'Статья'; // shown in the breadcrumb on the create view
    }
}