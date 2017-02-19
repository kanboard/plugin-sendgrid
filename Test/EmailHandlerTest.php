<?php

require_once 'tests/units/Base.php';

use Kanboard\Plugin\Sendgrid\EmailHandler;
use Kanboard\Model\TaskFinderModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\ProjectUserRoleModel;
use Kanboard\Model\UserModel;
use Kanboard\Core\Security\Role;

class SendgridTest extends Base
{
    public function testSendEmail()
    {
        $this->container['httpClient']
            ->expects($this->once())
            ->method('postJsonAsync')
            ->with(
                EmailHandler::API_URL,
                $this->anything(),
                $this->anything()
            );

        $emailHandler = new EmailHandler($this->container);
        $emailHandler->sendEmail('test@localhost', 'Me', 'Test', 'Content', 'Bob');
    }

    public function testSendEmailWithAuthorEmail()
    {
        $this->container['httpClient']
            ->expects($this->once())
            ->method('postJsonAsync')
            ->with(
                EmailHandler::API_URL,
                $this->contains(array('email' => 'bob@localhost')),
                $this->anything()
            );

        $emailHandler = new EmailHandler($this->container);
        $emailHandler->sendEmail('test@localhost', 'Me', 'Test', 'Content', 'Bob', 'bob@localhost');
    }

    public function testHandlePayload()
    {
        $emailHandler = new EmailHandler($this->container);
        $projectModel = new ProjectModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);
        $userModel = new UserModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(2, $userModel->create(array('username' => 'me', 'email' => 'me@localhost')));

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));
        $this->assertEquals(2, $projectModel->create(array('name' => 'test2', 'email' => 'something+test1@localhost')));

        // Empty payload
        $this->assertFalse($emailHandler->receiveEmail(array()));

        // Unknown user
        $this->assertFalse($emailHandler->receiveEmail(array(
            'envelope' => '{"to":["a@b.c"],"from":"a.b.c"}',
            'subject' => 'Email task'
        )));

        // Project not found
        $this->assertFalse($emailHandler->receiveEmail(array(
            'envelope' => '{"to":["a@b.c"],"from":"me@localhost"}',
            'subject' => 'Email task'
        )));

        // User is not member
        $this->assertFalse($emailHandler->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task'
        )));

        $this->assertTrue($projectUserRoleModel->addUser(2, 2, Role::PROJECT_MEMBER));

        // The task must be created
        $this->assertTrue($emailHandler->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task'
        )));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('', $task['description']);
        $this->assertEquals(2, $task['creator_id']);

        // Html content
        $this->assertTrue($emailHandler->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task',
            'html' => '<strong>bold</strong> text',
        )));

        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**bold** text', $task['description']);
        $this->assertEquals(2, $task['creator_id']);

        // Text content
        $this->assertTrue($emailHandler->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task',
            'text' => '**bold** text',
        )));

        $task = $taskFinderModel->getById(3);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**bold** text', $task['description']);
        $this->assertEquals(2, $task['creator_id']);

        // Text + html content
        $this->assertTrue($emailHandler->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task',
            'html' => '<strong>bold</strong> html',
            'text' => '**bold** text',
        )));

        $task = $taskFinderModel->getById(4);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**bold** html', $task['description']);
        $this->assertEquals(2, $task['creator_id']);
    }
}
