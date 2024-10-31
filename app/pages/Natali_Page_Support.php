<?php

class Natali_Page_Support extends Natali_Abstract_Page
{
    public function render($params = []) {

        $this->page_title = __('Поддержка', 'wp-natali');
        $this->template = 'pages/support';
        $this->page = 'natali_support';

        $this->content = [
            'log_file' => [
                'path' => NATALI_PLUGIN_DIR . '/log/log',
                'url'  => NATALI_PLUGIN_URL . '/log/log'
            ]
        ];

        if (file_exists($this->content['log_file']['path'])) {
            $log_file = _sanitize_text_fields(file_get_contents($this->content['log_file']['path']), true);
            $this->content['log_file']['body'] = '<pre class="natali-code">' . $log_file . '</pre>';
        }

        parent::render($this->getData());
    }
}