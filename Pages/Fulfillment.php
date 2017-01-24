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
