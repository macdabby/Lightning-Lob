<?php

namespace Modules\Lob\Model;

use Lightning\Model\URL;
use Lightning\Tools\Messenger;
use Lightning\Tools\Template;
use Modules\Checkout\Model\LineItem;

class Renderer {

    const HTML = 'html';
    const IMAGE_URL = 'image_url';

    /**
     * @var LineItem
     */
    protected $item;
    
    public function __construct(LineItem $item) {
        $this->item = $item;
    }
    
    public function getFront() {
        return $this->getSide('front');
    }
    
    public function getBack() {
        return $this->getSide('back');
    }

    public function previewFront() {
        return $this->getPreview('front');
    }

    public function previewBack() {
        return $this->getPreview('back');
    }

    /**
     * @param $side
     * @return string
     *   This can either be a full url to an image, or html content.
     */
    protected function getSide($side) {
        $print_file = $this->item->getAggregateOption('print_' . $side);
        if (empty($print_file) && $side == 'front') {
            Messenger::error('Front print file not found');
        }
        $output['type'] = null;
        if (!empty($print_file['url'])) {
            $output['type'] = self::IMAGE_URL;
            $output['content'] = URL::getAbsolute($print_file['url']);
        } elseif (!empty($print_file['template'])) {
            $template = new Template();
            $vars = (!empty($this->item->getProduct()->options['print_vars']) ? $this->item->getProduct()->options['print_vars'] : []) + $this->item->options;
            $template->setData($vars);
            $output['type'] = self::HTML;
            $output['content'] = $template->render($print_file['template'], true);
        }
        return $output;
    }

    protected function getPreview($side) {
        $content = $this->getSide($side);
        switch ($content['type']) {
            case self::HTML:
                return $content['content'];
            case self::IMAGE_URL:
                return '<img src="' . $content['content'] . '" width="100%" height="100%">';
        }
    }
}
