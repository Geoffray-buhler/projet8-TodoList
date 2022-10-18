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
        $userLogin = $userRepository->findOneBy(['username'=>'test']);
        $this->client->loginUser($userLogin);
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a.pull-right.btn.btn-danger', 'Se déconnecter');
    }

    //Test des vue Tasks.
    public function testTaskView(): void
    {
        $this->client->request('GET','/tasks');
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $userRepository = $this->entityManager->getRepository(User::class);
        $userLogin = $userRepository->findOneBy(['username'=>'test']);
        $tasks = $taskRepository->findBy(['user'=> $userLogin]);

        var_dump($tasks);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('img.slide-image', '');
    }

    //Test de la creations d'une tasks.
    public function testCreateTasks(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userLogin = $userRepository->findOneBy(['username'=>'test']);

        $task = new Task();
        $task->setTitle('Test');
        $task->setContent("C'est un test biensur");
        $task->setUser($userLogin);

        $taskManager = $this->createMock(Task::class);
        $taskManager->expects($this->any())->method('find')->willReturn($task);

        $this->client->request('GET','/tasks');
    }

    //Test de la creations d'un utilisateur.
    public function testCreateUsersAdmin(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userLogin = $userRepository->findOneBy(['username'=>'test']);

        $user = new User();
        $user->setEmail('testtest');
        $user->setUsername('testtest');
        $password = $this->pswEncoder->hash($user, '123456');
        $user->setPassword($password);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a.pull-right.btn.btn-danger', 'Se déconnecter');

        $this->client->request('GET','/tasks');
    }

        //Test de la creations d'un utilisateur.
        public function testCreateUsersSimpleUser(): void
        {
            $userRepository = $this->entityManager->getRepository(User::class);
            $userLogin = $userRepository->findOneBy(['username'=>'test']);
            
            $user = new User();
            $user->setEmail('testtest');
            $user->setUsername('testtest');
            $password = $this->pswEncoder->hash($user, '123456');
            $user->setPassword($password);
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('a.pull-right.btn.btn-danger', 'Se déconnecter');

            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('a.pull-right.btn.btn-danger', 'Se déconnecter');

            $this->client->request('GET','/tasks');
        }
}
