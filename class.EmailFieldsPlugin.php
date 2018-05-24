<?php

require_once(INCLUDE_DIR.'class.signal.php');
require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

/**
 * Class EmailFieldsPlugin
 *
 * Provides ability to set standard and dynamic fields based on email headers
 */
class EmailFieldsPlugin extends Plugin {
    var $config_class = 'EmailFieldsPluginConfig';
    private $columns = array();
    private $bIsEmail = false;

    function bootstrap() {
        // mail.processed occurs before model.created during fetching emails
        Signal::connect('mail.processed', array($this, 'onMailProcessed'));
        Signal::connect('model.created', array($this, 'onModelCreated'));
    }

    /**
     * Set up all possible variables before creation of ticket.
     * Will not work until https://github.com/osTicket/osTicket/issues/4287 is not fixed
     *
     * Check createTicket() function in /include/class.mailfetch.php
     * and create() function in /include/class.ticket.php for full list.
     *
     * The possible variables are:
     *  - name
     *  - email
     *  - subject
     *  - mid
     *  - header
     *  - in-reply-to
     *  - references
     *  - reply-to
     *  - reply-to-name
     *  - recipients
     *  - emailId
     *  - to-email-id
     *  - flags
     *  - message
     *  - thread-type
     *  - priorityId
     *  - attachments
     *  - duedate
     *  - time
     *  - topicId
     *  - autorespond
     *  - statusId
     *  - deptId
     *  - source
     *  - staffId
     *  - teamId
     *  - slaId
     *  - ip
     *  - cannedResponseId
     */
    function onMailProcessed(MailFetcher $mf, array &$vars) {
        $this->bIsEmail = true;
        $this->parseHeaders($vars['header']);
        foreach ($this->columns as $column => $value) {
            $vars[$column] = $value;
        }
    }

    function onModelCreated($object, $data) {
        if (!$this->bIsEmail) return;

        if (get_class($object) == 'DynamicFormEntryAnswer') {
            $column = $object->getField()->get('name');
            if (isset($this->columns[$column])) {
                $object->set('value', $this->columns[$column]);
                $object->save();
            }
        }
    }

    private function parseHeaders($raw_headers) {
        $headers = array();
        $available_headers = explode("\n", $this->getConfig()->get('headers'));
        foreach ($available_headers as $header) {
            list($header_name, $column) = explode(":", trim($header));
            $column = trim($column);
            $this->columns[$column] = null;
            $headers[$header_name] = $column;
        }

        $email_headers = explode("\n", $raw_headers);
        foreach ($email_headers as $header) {
            list($header_name, $value) = explode(":", trim($header));
            if (isset($headers[$header_name])) {
                $column = $headers[$header_name];
                $this->columns[$column] = trim($value);
            }
        }
    }
}