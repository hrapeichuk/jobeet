<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Category;
use AppBundle\Entity\Job;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Length;

class JobAdmin extends AbstractAdmin
{
    // setup the defaut sort column and order
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'expiresAt'
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('category', EntityType::class, ['class' => Category::class, 'choice_label' => 'name'])
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => Job::TYPES,
                    'choice_label' => function ($value) {
                        return 'types.'.$value;
                    },
                    'expanded' => true,
                    'constraints' => [new Length(['min' => 3])],
                ]
            )
            ->add('company')
            ->add('logo', FileType::class, ['required' => false, 'label' => 'Company logo', 'data_class' => null])
            ->add('url', null, ['required' => false])
            ->add('position')
            ->add('location')
            ->add('description')
            ->add('howToApply', null, ['label' => 'How to apply?'])
            ->add('isPublic', null, ['label' => 'Public?'])
            ->add('email');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('category', null, [], 'entity', [
                'class'    => 'AppBundle\Entity\Category',
                'choice_label' => 'name',
            ])
            ->add('company')
            ->add('position')
            ->add('description')
            ->add('isActivated')
            ->add('isPublic')
            ->add('email')
            ->add('expiresAt');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('company')
            ->add('position')
            ->add('location')
            ->add('url')
            ->add('isActivated')
            ->add('email')
            ->add('category.name')
            ->add('expiresAt')
            ->add('_action', 'actions', [
                'actions' => [
                    'view' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('category')
            ->add('type')
            ->add('company')
            ->add('logo', 'string', ['template' => 'admin/job/list_image.html.twig'])
            ->add('url')
            ->add('position')
            ->add('location')
            ->add('description')
            ->add('howToApply')
            ->add('isPublic')
            ->add('isActivated')
            ->add('token')
            ->add('email')
            ->add('expiresAt');
    }

    public function getBatchActions()
    {
        // retrieve the default (currently only the delete action) actions
        $actions = parent::getBatchActions();
        // check user permissions
        if ($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['extend'] = [
                'label'            => 'Extend',
                'ask_confirmation' => true // If true, a confirmation will be asked before performing the action
            ];
            $actions['deleteNeverActivated'] = [
                'label'            => 'Delete never activated jobs',
                'ask_confirmation' => true // If true, a confirmation will be asked before performing the action
            ];
        }
        return $actions;
    }
}
