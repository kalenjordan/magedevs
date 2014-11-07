<?php

namespace Controller;

use \Controller\Controller;

class Index extends Controller
{
    public function get()
    {
        $developers = $this->_getDevelopers();

        echo $this->_getTwig()->render('index.html.twig', array(
            'developers'    => $developers,
            'session'       => $this->_getSession(),
            'local_config'  => $this->_getContainer()->LocalConfig(),
        ));
    }

    protected function _getDevelopers()
    {
        $query = $this->_getContainer()->User()->selectAll();
        $userRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($query);

        $userModels = array();
        foreach ($userRows as $userRow) {
            $userModel = $this->_getContainer()->User()->setData($userRow);
            if ($this->_shouldIncludeUser($userModel)) {
                $userModels[] = $userModel;
            }
        }

        return $userModels;
    }

    /**
     * @param $user Model_User
     * @return bool
     */
    protected function _shouldIncludeUser($user)
    {
        if (isset($_GET['country'])) {
            $country = preg_replace('~[^A-Za-z]~','', $_GET['country']);
            if ($user->getDetail('country') == $country) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}