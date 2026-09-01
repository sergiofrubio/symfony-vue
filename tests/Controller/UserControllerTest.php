<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\InvoiceFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        // load fixtures
        $loader = new Loader();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $loader->addFixture(new UserFixtures($passwordHasher));
        $loader->addFixture(new InvoiceFixtures());

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());
    }

    private function login(string $email, string $password): string
    {
        $this->client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['email' => $email, 'password' => $password]));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), 'Login should succeed');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        return $data['token'];
    }

    public function testListUsersAsAdmin()
    {
        $token = $this->login('admin@example.com', 'adminpass');

        $this->client->request('GET', '/api/users', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $resp = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
        $data = json_decode($resp->getContent(), true);
        $this->assertArrayHasKey('items', $data);
        $this->assertGreaterThanOrEqual(1, count($data['items']));
    }

    public function testCreateGetUpdateDeleteUser()
    {
        $token = $this->login('admin@example.com', 'adminpass');

        // Create
        $payload = ['email' => 'newuser@example.com', 'password' => 'pass123', 'roles' => ['ROLE_USER'], 'is_active' => true];
        $this->client->request('POST', '/api/users', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token, 'CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $created = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $created);
        $id = $created['id'];

        // Get
        $this->client->request('GET', '/api/users/'.$id, [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $got = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('newuser@example.com', $got['email']);

        // Update
        $update = ['email' => 'updated@example.com', 'is_active' => false];
        $this->client->request('PUT', '/api/users/'.$id, [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token, 'CONTENT_TYPE' => 'application/json'], json_encode($update));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/api/users/'.$id, [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $got2 = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('updated@example.com', $got2['email']);

        // Delete
        $this->client->request('DELETE', '/api/users/'.$id, [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Ensure not found
        $this->client->request('GET', '/api/users/'.$id, [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
}
