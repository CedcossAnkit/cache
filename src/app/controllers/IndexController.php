<?php

use Phalcon\Mvc\Controller;

use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;

// require __DIR__ . '../../../public/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class IndexController extends Controller
{
    public function indexAction()
    {
    }

    public function addRolesAction()
    {
        if ($this->request->isPost()) {
            $role = new Roles();
            $obj = new App\Components\Myescaper();

            $inputData = array(
                'role_field' => $obj->sanitize($this->request->getPost('role_field')),
            );

            $role->assign(
                $inputData,
                [
                    'role_field'
                ]
            );

            $success = $role->save();

            $this->view->success = $success;

            if ($success) {
                $this->view->message =$this->locale->_("Role added successfully");
            } else {
                $this->mainLogger->error("Role not added due to following reason: <br>" . implode("<br>", $role->getMessages()));
                $this->view->message = "Role not added due to following reason: <br>" . implode("<br>", $role->getMessages());
            }
        }
    }

    public function addComponentsAction()
    {
        if ($this->request->isPost()) {
            $component = new Components();
            $obj = new App\Components\Myescaper();

            $inputData = array(
                'controller' => $obj->sanitize($this->request->getPost('controller')),
                'action' => $obj->sanitize($this->request->getPost('action'))
            );

            $component->assign(
                $inputData,
                [
                    'controller',
                    'action'
                ]
            );

            $success = $component->save();

            $this->view->success = $success;

            if ($success) {
                $this->view->message = $this->locale->_("Component added successfully");
            } else {
                $this->mainLogger->error("Component not added due to following reason: <br>" . implode("<br>", $component->getMessages()));
                $this->view->message = "Component not added due to following reason: <br>" . implode("<br>", $component->getMessages());
            }
        }
    }

    public function ACLAction()
    {
    }

    public function addUserAction()
    {
        // $cache=$this->cache;
        // print_r($this->cache);
        // die;
        if ($this->request->isPost()) {
            $user = new Users();
            $obj = new App\Components\Myescaper();

            $inputData = array(
                'username' => $obj->sanitize($this->request->getPost('username')),
                'email' => $obj->sanitize($this->request->getPost('email'))

            );

            $token = $this->CreateTokenAction($this->request->getPost('role_field'), $this->request->getPost('username'));

            $user->assign(
                $inputData,
                [
                    'username',
                    'email'
                ],
                $user->role_field = $token
            );


            $success = $user->save();

            $this->view->success = $success;

            if ($success) {
                $message = $this->locale->_("User added successfully");
                $text = $this->locale->_(
                    $message,
                    [
                        'name' => $message,
                    ]
                );
                $this->view->message = $text;

            } else {
                $this->mainLogger->error("User not added due to following reason: <br>" . implode("<br>", $user->getMessages()));
                $this->view->message = "User not added due to following reason: <br>" . implode("<br>", $user->getMessages());
            }
        }
    }

    public function CreateTokenAction($role, $username)
    {
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000,
            "role" => $role,
            "username" => $username
        );

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        $jwt = JWT::encode($payload, $key, 'HS256');

        return $jwt;
    }

    public function justForTestAction()
    {
        $name = 'Mike';

        $text = $this->locale->_(
            'hi-name',
            [
                'name' => $name,
            ]
        );
        
        $this->view->text = $text;
    }

    
}
