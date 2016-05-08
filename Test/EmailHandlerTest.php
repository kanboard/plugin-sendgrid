<?php

require_once 'tests/units/Base.php';

use Kanboard\Plugin\Sendgrid\EmailHandler;
use Kanboard\Model\TaskCreation;
use Kanboard\Model\TaskFinder;
use Kanboard\Model\Project;
use Kanboard\Model\ProjectUserRole;
use Kanboard\Model\User;
use Kanboard\Core\Security\Role;

class SendgridTest extends Base
{
    public function testSendEmail()
    {
        $this->container['httpClient']
            ->expects($this->once())
            ->method('postForm')
            ->with(
                'https://api.sendgrid.com/api/mail.send.json',
                $this->anything(),
                $this->anything()
            );

        $pm = new EmailHandler($this->container);
        $pm->sendEmail('test@localhost', 'Me', 'Test', 'Content', 'Bob');
    }

    public function testHandlePayload()
    {
        $w = new EmailHandler($this->container);
        $p = new Project($this->container);
        $pp = new ProjectUserRole($this->container);
        $u = new User($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFinder($this->container);

        $this->assertEquals(2, $u->create(array('username' => 'me', 'email' => 'me@localhost')));

        $this->assertEquals(1, $p->create(array('name' => 'test1')));
        $this->assertEquals(2, $p->create(array('name' => 'test2', 'identifier' => 'TEST1')));

        // Empty payload
        $this->assertFalse($w->receiveEmail(array()));

        // Unknown user
        $this->assertFalse($w->receiveEmail(array(
            'envelope' => '{"to":["a@b.c"],"from":"a.b.c"}',
            'subject' => 'Email task'
        )));

        // Project not found
        $this->assertFalse($w->receiveEmail(array(
            'envelope' => '{"to":["a@b.c"],"from":"me@localhost"}',
            'subject' => 'Email task'
        )));

        // User is not member
        $this->assertFalse($w->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task'
        )));

        $this->assertTrue($pp->addUser(2, 2, Role::PROJECT_MEMBER));

        // The task must be created
        $this->assertTrue($w->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task'
        )));

        $task = $tf->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('', $task['description']);
        $this->assertEquals(2, $task['creator_id']);

        // Html content
        $this->assertTrue($w->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task',
            'html' => '<strong>bold</strong> text',
        )));

        $task = $tf->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**bold** text', $task['description']);
        $this->assertEquals(2, $task['creator_id']);

        // Text content
        $this->assertTrue($w->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task',
            'text' => '**bold** text',
        )));

        $task = $tf->getById(3);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**bold** text', $task['description']);
        $this->assertEquals(2, $task['creator_id']);

        // Text + html content
        $this->assertTrue($w->receiveEmail(array(
            'envelope' => '{"to":["something+test1@localhost"],"from":"me@localhost"}',
            'subject' => 'Email task',
            'html' => '<strong>bold</strong> html',
            'text' => '**bold** text',
        )));

        $task = $tf->getById(4);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('Email task', $task['title']);
        $this->assertEquals('**bold** html', $task['description']);
        $this->assertEquals(2, $task['creator_id']);
    }
}
