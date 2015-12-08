<?php

namespace Kanboard\Plugin\Sendgrid;

use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;

/**
 * Sendgrid Plugin
 *
 * @package  sendgrid
 * @author   Frederic Guillot
 */
class Plugin extends Base
{
    public function initialize()
    {
        $this->emailClient->setTransport('sendgrid', '\Kanboard\Plugin\Sendgrid\EmailHandler');
        $this->template->hook->attach('template:config:integrations', 'sendgrid:integration');

        $this->on('app.bootstrap', function($container) {
            Translator::load($container['config']->getCurrentLanguage(), __DIR__.'/Locale');
        });
    }

    public function getPluginDescription()
    {
        return 'Sendgrid Email Integration';
    }

    public function getPluginAuthor()
    {
        return 'Frédéric Guillot';
    }

    public function getPluginVersion()
    {
        return '1.0.1';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/kanboard/plugin-sendgrid';
    }
}
