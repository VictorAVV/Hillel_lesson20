<?php
namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\UserBundle\Form\Type\SecurityRolesType;

final class UserAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('name', TextType::class, [
                    'label' => 'Имя',
                ])
                ->add('email', TextType::class, [
                    'label' => 'Email',
                ])
                ->add('userPassword', TextType::class, [
                    'label' => 'Пароль',
                ])

                ->add('createdAt', null, [
                    'label' => 'Дата регистрации',
                ])
            ->end()
            ->with('Management')
                //->add('roles', SecurityRolesType::class, [ 'multiple' => true ])
                ->add('enabled', null, [
                    'label' => 'enabled',
                ])      
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->addIdentifier('name', null, [
                'label' => 'Имя',
            ])
            ->addIdentifier('email', null, [
                'label' => 'Email',
            ])
            ->addIdentifier('enabled', null, [
                'label' => 'Enabled',
            ])
            /*->addIdentifier('roles', null, [
                'label' => 'roles',
            ])*/
            ->add('createdAt', null, [
                'format' => 'Y-m-d H:i:s',
                'label' => 'Дата регистрации',
            ])
        ;
    }

    public function toString($object)
    {
        return $object instanceof User
            ? $object->getName()
            : 'Статья'; // shown in the breadcrumb on the create view
    }

}