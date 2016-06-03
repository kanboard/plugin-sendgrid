<?php

namespace Kanboard\Plugin\Sendgrid\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Plugin\Sendgrid\EmailHandler;

/**
 * Webhook Controller
 *
 * @package  sendgrid
 * @author   Frederic Guillot
 */
class WebhookController extends BaseController
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
        $this->response->text($handler->receiveEmail($_POST) ? 'PARSED' : 'IGNORED');
    }
}
