<?php

namespace App\Tests;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTestNPTest extends WebTestCase
{
    private KernelBrowser|null $client = null;
    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    //Test de connexion.
    public function testLogIn(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        $userLogin = $userRepository->findOneBy(['username'=>'test','email'=>'test@test.com']);
        
        $this->client->loginUser($userLogin);

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a.pull-right.btn.btn-danger', 'Se déconnecter');
    }

    //Test des vue Tasks.
    // administration et vue des autres tache 
    public function testTaskView(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        $userLogin = $userRepository->findOneBy(['username'=>'test','email'=>'test@test.com']);

        $this->client->loginUser($userLogin);
        $this->client->request('GET','/tasks');

        $taskRepository = $this->entityManager->getRepository(Task::class);

        $tasks = $taskRepository->findBy(['user'=> $userLogin]);

        $this->assertResponseIsSuccessful();
        foreach ($tasks as $task) {
            $this->assertSelectorTextContains('a[href="/tasks/'.$task->getId().'/edit"]', $task->getTitle());
        }
    }

    //Test de la creations d'une tasks.
    public function testCreateTasksIfNotConnected(): void
    {

        $this->client->request('GET','/tasks/create');
        $this->assertResponseRedirects('/login',302);

    }

    //Test de la creations d'une tasks.
    public function testCreateTasksIfConnectedSimpleUser(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userLogin = $userRepository->findOneBy(['username'=>'test']);
        $this->client->loginUser($userLogin);

        $crawler = $this->client->request('GET','/tasks/create');

        $buttonCrawlerNode = $crawler->selectButton('Ajouter');

        $form = $buttonCrawlerNode->form();

        $form['task[title]'] = 'Magnifique';
        $form['task[content]'] = 'Ce test est magnifique !';
        
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks',302);
    }

    //Test de suppression d'une tasks. (en admin)
    public function testDeleteTasks(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $taskRepository = $this->entityManager->getRepository(Task::class);
        
        $userLogin = $userRepository->findOneBy(['username'=>'test']);
        $this->client->loginUser($userLogin);

        $crawler = $this->client->request('GET','/tasks');

        $task = $taskRepository->findOneBy(['title'=>'Magnifique','content'=>'Ce test est magnifique !']); 

        $buttonCrawlerNode = $crawler->selectButton('Supprimer');

        $form = $buttonCrawlerNode->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks',302);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'La tâche a bien été supprimée.');
    }

    //Jusqu'à la ok !

    //Test de la creations d'un utilisateur.
    public function testCreateUsersNotLogin(): void
    {
        $crawler = $this->client->request('GET','/users/create');

        $buttonCrawlerNode = $crawler->selectButton('Ajouter');

        $form = $buttonCrawlerNode->form();

        $form['user[username]'] = 'testouille';
        $form['user[password][first]'] = '123456';
        $form['user[password][second]'] = '123456';
        $form['user[email]'] = 'testouille@test.com';
        
        $this->client->submit($form);

        $this->assertResponseRedirects('/users',302);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-danger', 'Vous ne pouvez pas créer un utilisateur.');
    }

    //Test de la creations d'un utilisateur.
    public function testCreateUsersAdmin(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        
        $userLogin = $userRepository->findOneBy(['username'=>'test']);
        $this->client->loginUser($userLogin);

        $crawler = $this->client->request('GET','/users/create');

        $buttonCrawlerNode = $crawler->selectButton('Ajouter');

        $form = $buttonCrawlerNode->form();

        $form['user[username]'] = 'testouille';
        $form['user[password][first]'] = '123456';
        $form['user[password][second]'] = '123456';
        $form['user[email]'] = 'testouille@test.com';
        
        $this->client->submit($form);

        $this->assertResponseRedirects('/users',302);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', "L'utilisateur a bien été ajouté.");
    }
}
