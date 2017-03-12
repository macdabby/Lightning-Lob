<?php

namespace Modules\Lob\Pages;

use Exception;
use Lightning\Tools\Messenger;
use Lightning\Tools\Navigation;
use Lightning\View\Page;
use Lightning\Tools\ClientUser;
use Lightning\Tools\Communicator\RestClient;
use Lightning\Tools\Configuration;
use Lightning\Tools\Request;
use Lightning\Tools\Template;
use Modules\Checkout\Model\LineItem;
use Modules\Checkout\Model\Order;
use Modules\Lob\Connector\Checkout;
use Modules\Lob\Model\Renderer;

class Fulfillment extends Page {

    protected $rightColumn = false;
    protected $page = ['fulfillment', 'Lob'];
    protected $share = false;

    public function hasAccess() {
        return ClientUser::requireAdmin();
    }

    public function get() {
        $order = Order::loadByID(Request::get('id', Request::TYPE_INT));
        Template::getInstance()->set('order', $order);
    }

    public function getPreview() {
        $line_item_id = Request::get('line_item_id', Request::TYPE_INT);

        if ($side = Request::get('side')) {
            $line_item = LineItem::loadByID($line_item_id);
            if (empty($line_item)) {
                throw new Exception('Item not found!');
            }
            $renderer = new Renderer($line_item);
            if ($side == 'front') {
                echo $renderer->previewFront();
            } else {
                echo $renderer->previewBack();
            }
            exit;
        } else {
            $this->page = ['preview', 'Lob'];
            Template::getInstance()->set('line_item_id', $line_item_id);
        }
    }

    public function post() {
        $order = Order::loadByID(Request::post('id', Request::TYPE_INT));
        if (empty($order)) {
            throw new Exception('Could not load order.');
        }

        // Send to Lob.
        $connector = new Checkout();
        if ($connector->fulfillOrder($order)) {
            Messenger::message('The order has been processed.');
            Navigation::redirect('/admin/orders?id=' . $order->id);
        } else {
            throw new Exception('There was a problem submitting the order.');
        }
    }
}
