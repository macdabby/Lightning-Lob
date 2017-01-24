<?php

namespace Modules\Lob\Connector;

use Exception;
use Lightning\Model\URL;
use Lightning\Tools\Communicator\RestClient;
use Lightning\Tools\Configuration;
use Lightning\Tools\Messenger;
use Lightning\Tools\Template;
use Modules\Checkout\Model\LineItem;
use Modules\Checkout\Model\Order;

class Checkout {

    const FULFILLMENT_URL = '/admin/orders/fulfillment/lob';
    const FULLFILLMENT_TEXT = 'Mail with Lob';
    const API_ENDPOINT = 'https://api.lob.com/v1';

    /**
     * @param Order $order
     *
     * @return boolean
     *
     * @throws Exception
     */
    public function fulfillOrder($order) {
        if (Configuration::get('debug')) {
            $api_key = Configuration::get('modules.lob.test_api_key');
        } else {
            $api_key = Configuration::get('modules.lob.production_api_key');
        }
////        $api_key = 'live_cc37a577f9dbe8fb72d43c9d7b724dff2fc';
//        $api_key = 'test_6f723525b9da6bf0a1a2615091d415a1fc1';

        // Prepare the connection.
        $client = new RestClient('https://api.lob.com/v1');
        $client->sendJSON(true);
        $client->setBasicAuth($api_key, '');

        // Load the items.
        $items = $order->getItemsToFulfillWithHandler('lob');
        if (empty($items)) {
            throw new Exception('No items to ship.');
        }

        // Try to fulfill each item.
        foreach ($items as $item) {
            /* @var LineItem $item */

            $client->set('to', [
                'name' => $item->options['to'],
                'address_line1' => $item->options['address'],
                'address_line2' => $item->options['address2'],
                'address_city' => $item->options['city'],
                'address_state' => $item->options['state'],
                'address_zip' => strval($item->options['zip']),
                'address_country' => !empty($item->options['country']) ? $item->options['country'] : 'US',
            ]);
            $from_address = Configuration::get('modules.lob.from_address');
            $from_address['address_zip'] = strval($from_address['address_zip']);
            $client->set('from', $from_address);

            // Prepare the printing options.
            $client->set('size', $item->getAggregateOption('print_size', '6x9'));
            foreach (['front', 'back'] as $side) {
                $print_file = $item->getAggregateOption('print_' . $side);
                if (empty($print_file) && $side == 'front') {
                    Messenger::error('Front print file not found');
                }
                if (!empty($print_file['url'])) {
                    $print_file = URL::getAbsolute($print_file['url']);
                } elseif (!empty($print_file['template'])) {
                    $template = new Template();
                    $vars = (!empty($item->getProduct()->options['print_vars']) ? $item->getProduct()->options['print_vars'] : []) + $item->options;
                    $template->setData($vars);
                    $print_file = $template->render($print_file['template'], true);
                }
                $client->set($side, $print_file);
            }

            try {
                $result = $client->callPost('/postcards');
                // TODO: Save this ID to the line item.
                $id = $client->get('id');
            } catch (Exception $e) {
                Messenger::error($e->getMessage());
            }
            if ($result && !empty($id)) {
                $item->markFulfilled();
            }
        }
        $order->markFullfilled();
    }
}
