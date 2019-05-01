<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class CommentAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('content')
            ->add('user', null, [], EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
            ])
            ->add('datetime')
            ->add('parent_comment')
            ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add('content')
            ->add('user.name', null, [
                'label' => 'Автор',
            ])
            ->add('datetime')
            ->add('article.title')
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('id')
            ->add('content')
            ->add('user', EntityType::class, [
                'label' => 'Автор',
                'class' => User::class,
                'choice_label' => 'name',
                'required' => true,
            ])
            ->add('parent_comment')
            ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('content')
            ->add('user.name')
            ->add('datetime')
            ->add('parent_comment')
            ;
    }
}
