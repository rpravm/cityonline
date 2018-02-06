<?php
// src/Security/User/CityonlineUser.php
namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class CityonlineUser implements UserInterface
{
    private $id;

    private $username;
    private $password;
    private $session_data;

    public function __construct($username, $password, $session_data)
    {
        $this->username = $username;
        $this->password = $password;
        $this->session_data = $session_data;
    }

    public function getUsername()
    {
        return $this->username;
    }
    public function getSessionData()
    {
        return $this->session_data;
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