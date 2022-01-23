<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;
use App\Entity\Categories;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;


class TaskController extends AbstractController
{
    #[Route('/task', name: 'task')]
    public function index(): Response
    {
        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
        ]);
    }

    #[Route('/task/create', name: 'task_create')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        // creates a task object and initializes some data for this example
        $task = new Task();
        $task->setNameTask('Write a blog post');
        $task->setDueDateTask(new \DateTime('tomorrow'));
        $task->setDescriptionTask('Write a description here');
        

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class,['label' => 'Nom', 'attr' => ['class' => 'form-control']])
            ->add('dueDateTask', DateType::class,["widget"=>"single_text",'attr' => ['class' => 'form-control']])
            ->add('descriptionTask', TextareaType::class,['attr' => ['class' => 'form-control']])
            
            ->add('priorityTask', ChoiceType::class, [
                'attr' => ['class' => 'form-select'],
                    'choices'  => [
                        'Haute' => 'Haute',
                        'Normale' => 'Normal',
                        'Basse' => 'Basse',
                    ],
            ])

            ->add('category', EntityType::class, [
                // looks for choices from this entity
                'class' => Categories::class,
            
                // uses the User.username property as the visible option string
                'choice_label' => 'libelleCategory',
            
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])

            ->add('save', SubmitType::class, ['label' => 'Create Task','attr' => ['class' => 'btn btn-primary']])
            ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                // but, the original `$task` variable has also been updated
                $task = $form->getData();

            // ... perform some action, such as saving the task to the database
            $task->setCreatedDateTask(new \DateTime('today'));
            // tell Doctrine you want to (eventually) save the task (no queries yet)
            $entityManager->persist($task);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            return $this->redirectToRoute('task_listing');
        }

        return $this->renderForm('task/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/listing', name: 'task_listing')]
    public function listing(ManagerRegistry $doctrine): Response
    {
        $tasks = $doctrine->getRepository(Task::class)->findAll();

        return $this->render('task/listing.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    
    #[Route("/task/edit/{id}", name:'task_edit')]
    
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        // creates a task object and initializes some data for this example
        $task = $entityManager->getRepository(Task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class,['label' => 'Nom', 'attr' => ['class' => 'form-control']])
            ->add('dueDateTask', DateType::class,["widget"=>"single_text",'attr' => ['class' => 'form-control']])
            ->add('descriptionTask', TextareaType::class,['attr' => ['class' => 'form-control']])
            
            ->add('priorityTask', ChoiceType::class, [
                'attr' => ['class' => 'form-select'],
                    'choices'  => [
                        'Haute' => 'Haute',
                        'Normale' => 'Normal',
                        'Basse' => 'Basse',
                    ],
            ])

            ->add('category', EntityType::class, [
                // looks for choices from this entity
                'class' => Categories::class,
            
                // uses the User.username property as the visible option string
                'choice_label' => 'libelleCategory',
            
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])

            ->add('save', SubmitType::class, ['label' => 'Update Task','attr' => ['class' => 'btn btn-primary']])
            ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                // but, the original `$task` variable has also been updated
                $task = $form->getData();

                // ... perform some action, such as saving the task to the database
                $task->setCreatedDateTask(new \DateTime('today'));
                // tell Doctrine you want to (eventually) save the task (no queries yet)
                $entityManager->persist($task);

                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();

                return $this->redirectToRoute('task_listing');
            } 

        return $this->renderForm('task/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/delete/{id}', name: 'task_delete')]
    public function remove(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);
        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('task_listing');
    }
}
