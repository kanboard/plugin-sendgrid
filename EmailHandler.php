<?php

namespace Kanboard\Plugin\Sendgrid;

require_once __DIR__.'/vendor/autoload.php';

use Exception;
use Kanboard\Core\Base;
use Kanboard\Core\Mail\ClientInterface;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Sendgrid Mail Handler
 *
 * @package  sendgrid
 * @author   Frederic Guillot
 */
class EmailHandler extends Base implements ClientInterface
{
    /**
     * Get API token
     *
     * @access public
     * @return string
     */
    public function getApiKey()
    {
        if (defined('SENDGRID_API_KEY')) {
            return SENDGRID_API_KEY;
        }

        return $this->configModel->get('sendgrid_api_key');
    }

    /**
     * Get API user
     *
     * @access public
     * @return string
     */
    public function getApiUser()
    {
        if (defined('SENDGRID_API_USER')) {
            return SENDGRID_API_USER;
        }

        return $this->configModel->get('sendgrid_api_user');
    }

    /**
     * Send a HTML email
     *
     * @access public
     * @param  string  $email
     * @param  string  $name
     * @param  string  $subject
     * @param  string  $html
     * @param  string  $author
     */
    public function sendEmail($email, $name, $subject, $html, $author)
    {
        $payload = array(
            'api_user' => $this->getApiUser(),
            'api_key' => $this->getApiKey(),
            'to' => $email,
            'toname' => $name,
            'from' => $this->helper->mail->getMailSenderAddress(),
            'fromname' => $author,
            'html' => $html,
            'subject' => $subject,
        );

        $this->httpClient->postFormAsync('https://api.sendgrid.com/api/mail.send.json', $payload);
    }

    /**
     * Parse incoming email
     *
     * @access public
     * @param  array   $payload   Incoming email
     * @return boolean
     */
    public function receiveEmail(array $payload)
    {
        if (empty($payload['envelope']) || empty($payload['subject'])) {
            return false;
        }

        $envelope = json_decode($payload['envelope'], true);
        $recipient = isset($envelope['to'][0]) ? $envelope['to'][0] : '';

        // The user must exists in Kanboard
        $user = $this->userModel->getByEmail($envelope['from']);

        if (empty($user)) {
            $this->logger->debug(__METHOD__.': Ignored => user not found: '.$envelope['from']);
            return false;
        }

        // The project must have a short name
        $project = $this->projectModel->getByEmail($recipient);

        if (empty($project)) {
            $this->logger->debug(__METHOD__.': Ignored => project not found: '.$recipient);
            return false;
        }

        // The user must be member of the project
        if (! $this->projectPermissionModel->isAssignable($project['id'], $user['id'])) {
            $this->logger->debug(__METHOD__.': Ignored => user is not member of the project');
            return false;
        }

        // Finally, we create the task
        $taskId = $this->taskCreationModel->create(array(
            'project_id'  => $project['id'],
            'title'       => $this->helper->mail->filterSubject($payload['subject']),
            'description' => $this->getTaskDescription($payload),
            'creator_id'  => $user['id'],
            'swimlane_id' => $this->getSwimlaneId($project),
        ));

        if ($taskId > 0) {
            $this->addEmailBodyAsAttachment($taskId, $payload);
            $this->uploadAttachments($taskId, $payload);
            return true;
        }

        return false;
    }

    protected function getSwimlaneId(array $project)
    {
        $swimlane = $this->swimlaneModel->getFirstActiveSwimlane($project['id']);
        return empty($swimlane) ? 0 : $swimlane['id'];
    }

    protected function getTaskDescription(array $payload)
    {
        if (! empty($payload['html'])) {
            $htmlConverter = new HtmlConverter(array(
                'strip_tags'   => true,
                'remove_nodes' => 'meta script style link img span',
            ));

            return $htmlConverter->convert($payload['html']);
        } elseif (! empty($payload['text'])) {
            return $payload['text'];
        }

        return '';
    }

    protected function addEmailBodyAsAttachment($taskId, array $payload)
    {
        $filename = t('Email') . '.txt';
        $data = '';

        if (! empty($payload['html'])) {
            $data = $payload['html'];
            $filename = t('Email') . '.html';
        } elseif (! empty($payload['text'])) {
            $data = $payload['text'];
        }

        if (! empty($data)) {
            $this->taskFileModel->uploadContent($taskId, $filename, $data, false);
        }
    }

    protected function uploadAttachments($taskId, array $payload)
    {
        if (isset($payload['attachments']) && $payload['attachments'] > 0) {
            for ($i = 1; $i <= $payload['attachments']; $i++) {
                $this->uploadAttachment($taskId, 'attachment' . $i);
            }
        }
    }

    protected function uploadAttachment($taskId, $name)
    {
        $fileInfo = $this->request->getFileInfo($name);

        if (! empty($fileInfo)) {
            try {
                $this->taskFileModel->uploadFile($taskId, $fileInfo);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
