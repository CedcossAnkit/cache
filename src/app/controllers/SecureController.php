<?php

use Phalcon\Mvc\Controller;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;

use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

class SecureController extends Controller
{
    public function BuildAction()
    {
        if ($this->request->isPost()) {
            $arr = $this->request->getPost();
        }

        $aclFile = APP_PATH . '/security/acl.cache';

        if (true !== is_file($aclFile)) {
            // The ACL does not exist - build it
            $acl = new Memory();

            /**
             * Setup the ACL
             */
            
            $acl->addRole('admin');
            $acl->addRole('manager');
            $acl->addRole('guest');

            $acl->addComponent(
                'secure',
                [
                    'build'
                ]
            );
            $acl->addComponent(
                'Order',
                [
                    'settings'
                ]
            );
            // $acl->allow('manager', 'Order', 'settings');
            $acl->allow('admin', '*', '*');
            $acl->deny('guest', '*', '*');

            //Store serialized list into plain file
            file_put_contents(
                $aclFile,
                serialize($acl)
            );
        } else {
            //Restore ACL object fron serialized file
            // echo "assa";die;
            $acl = unserialize(
                file_get_contents($aclFile)
            );


            if (count($arr) > 0) {
                foreach ($arr['component'] as $key => $value) {
                    $acl->addRole($arr['role_field']);
                }


                foreach ($arr['component'] as $key => $value) {
                    $componentObj = Components::find($value);
                    $acl->addComponent(
                        $componentObj[0]->controller,
                        [
                            $componentObj[0]->action
                        ]
                    );
                }


                foreach ($arr['component'] as $key => $value) {
                    $componentObj = Components::find($value);

                    $acl->allow($arr['role_field'], $componentObj[0]->controller, $componentObj[0]->action);
                }
            }
            //Store serialized list into plain file
            file_put_contents(
                $aclFile,
                serialize($acl)
            );
        }
        // print_r($acl);
        die($this->locale->_("Granted Role Permissions"));
    }
}
