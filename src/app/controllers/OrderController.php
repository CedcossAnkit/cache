<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{

    public function IndexAction()
    {
    }

    public function placeorderAction()
    {
        $this->view->products = Products::find();
        if ($this->request->isPost()) {
            $order = new Orders();
            $obj = new App\Components\Myescaper();

            $inputData = array(
                'customer_name' => $obj->sanitize($this->request->getPost('customer_name')),
                'customer_address' => $obj->sanitize($this->request->getPost('customer_address')),
                'zipcode' => $obj->sanitize($this->request->getPost('zipcode')),
                'product' => $obj->sanitize($this->request->getPost('product')),
                'quantity' => $obj->sanitize($this->request->getPost('quantity'))
            );

            $order->assign(
                $inputData,
                [
                    'customer_name',
                    'customer_address',
                    'zipcode',
                    'product',
                    'quantity'
                ]
            );

            $success = $order->save();

            $this->view->success = $success;
            $id = json_decode(json_encode($order))->id;

            if ($success) {
                $eventsManager = $this->di->get('EventsManager');
                $eventsManager->fire('notifications:setDefaultZipcode', $this, $id);               
                $this->view->message = $this->locale->_("Order placed successfully");
            } else {
                $this->mainLogger->error("Order not placed due to following reason: <br>" . implode("<br>", $order->getMessages()));
                $this->view->message = "Order not placed due to following reason: <br>" . implode("<br>", $order->getMessages());
            }
        }
    }

    public function listOrdersAction()
    {
        $this->view->data = Orders::find();
    }

    public function settingsAction()
    {
        if ($this->request->isPost()) {
            $settings = new Settings();
            $settingsObj = Settings::find();
            $obj = new App\Components\Myescaper();
            
            $inputData = array(
                'title_optimization' => $obj->sanitize($this->request->getPost('title_optimization')),
                'default_price' => $obj->sanitize($this->request->getPost('default_price')),
                'default_stock' => $obj->sanitize($this->request->getPost('default_stock')),
                'default_zipcode' => $obj->sanitize($this->request->getPost('default_zipcode')),
            );
            
            if (count($settingsObj)<=0) {
                $settings->assign(
                    $inputData,
                    [
                        'title_optimization',
                        'default_price',
                        'default_stock',
                        'default_zipcode'
                    ]
                );
    
                $success = $settings->save();
    
                $this->view->success = $success;
    
                if ($success) {
                    $this->view->message = "Settings saved";
                } else {
                    $this->view->message = "Settings not saved due to following reason: <br>" . implode("<br>", $settings->getMessages());
                }
            } else {
                $settingsObj =  Settings::find([
                    'conditions' => 'id= :id:',
                    'bind' => [
                        'id' => 1,
                    ]
                ]);
                $settingsObj[0]->title_optimization = $inputData['title_optimization'];
                $settingsObj[0]->default_price = $inputData['default_price'];
                $settingsObj[0]->default_stock = $inputData['default_stock'];
                $settingsObj[0]->default_zipcode = $inputData['default_zipcode'];
                $success = $settingsObj[0]->update();
    
                $this->view->success = $success;
                if ($success) {
                    $this->view->message = "Settings updated";
                } else {
                    $this->view->message = "Settings not updated due to following reason: <br>" . implode("<br>", $settings->getMessages());
                }
            }
        }
    }
}
