<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class EmailFieldsPluginConfig extends PluginConfig {
    function __t($s) {
        return $s;
    }

    function getOptions() {
        return array(
            'sbf' => new SectionBreakField([
                'label' => $this->__t('List of allowed headers'),
                'hint' => $this->__t('List choices, one per line. Specify "header:field" to define relationship. For example, X-Email-Topic-Id:topicId'),
            ]),
            'headers' => new TextareaField(array(
                'label' => 'Headers',
                'configuration' => array(
                    'rows' => 5,
                    'cols' => 80,
                    'html' => false,
                )
            )),
        );
    }
}
