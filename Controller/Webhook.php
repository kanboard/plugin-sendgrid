<?php

namespace Kanboard\Plugin\Sendgrid\Controller;

use Kanboard\Controller\Base;
use Kanboard\Plugin\Sendgrid\EmailHandler;

/**
 * Webhook Controller
 *
 * @package  sendgrid
 * @author   Frederic Guillot
 */
class Webhook extends Base
{
    /**
     * Handle Sendgrid webhooks
     *
     * @access public
     */
    public function receiver()
    {
        $this->checkWebhookToken();

        $handler = new EmailHandler($this->container);
        echo $handler->receiveEmail($this->request->getJson()) ? 'PARSED' : 'IGNORED';
    }
}
