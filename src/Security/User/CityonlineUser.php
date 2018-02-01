<?php
// src/Security/User/CityonlineUser.php
namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class CityonlineUser implements UserInterface
{
    private $id;

    private $username;
    private $password;
    private $session;
    private $cnum;

    public function __construct($username, $password, $session, $cnum)
    {
        $this->username = $username;
        $this->password = $password;
        $this->session = $session;
        $this->cnum = $cnum;
    }

    public function getUsername()
    {
        return $this->username;
    }
    public function getSession()
    {
        return $this->session;
    }
    public function getCnum()
    {
        return $this->cnum;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getPassword()
    {
        return $this->password;
    }
    public function getSalt()
    {
    }
    public function eraseCredentials()
    {
    }

    // more getters/setters
}
?>