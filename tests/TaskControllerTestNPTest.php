<?php

namespace App\Tests;

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
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('img.slide-image', '');
    }

    //Test de la creations d'une tasks.
    public function testCreateTasks(): void
    {
        $this->client->request('GET','/tasks');
    }

    //Test de la creations d'un utilisateur.
    public function testCreateUsers(): void
    {
        $this->client->request('GET','/tasks');
    }
}
