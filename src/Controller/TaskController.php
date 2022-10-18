<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends CoreController
{
    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction()
    {
        return $this->render('task/list.html.twig', ['tasks' => $this->emi->getRepository(Task::class)->findBy(['user'=>$this->getUser()])]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->emi->persist($task);
            $task->setUser($this->getUser());
            $this->emi->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function editAction(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser() === $task->getUser() || in_array('ROLE_ADMIN',$this->getUser()->getRoles())) {
                $this->emi->flush();

                $this->addFlash('success', 'La tâche a bien été modifiée.');
            }else{
                $this->addFlash('error', 'Vous ne pouver pas modifier cette tache.');
            }
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction(Task $task)
    {
        if ($this->getUser() === $task->getUser() || in_array('ROLE_ADMIN',$this->getUser()->getRoles())) {
            $task->toggle(!$task->isDone());
            $this->emi->flush();
            if ($task->isDone()) {
                $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
            }else{
                $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme non-faite.', $task->getTitle()));
            }
            
        }else{
            $this->addFlash('error', 'Vous ne pouver pas modifier cette tache.');
        }

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(Task $task)
    {
        if ($this->getUser() === $task->getUser() || in_array('ROLE_ADMIN',$this->getUser()->getRoles())) {
            $this->emi->remove($task);
            $this->emi->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
        }else{
            $this->addFlash('error', 'Vous ne pouver pas supprimer cette tache.');
        }
        return $this->redirectToRoute('task_list');
    }
}
