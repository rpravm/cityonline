<?php
// src/Security/User/CityonlineUserProvider.php
namespace App\Security\User;

use App\Security\User\WebserviceUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\DBAL\Driver\Connection;

class CityonlineUserProvider implements UserProviderInterface
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadUserByUsername($username)
    {
        // make a call to your webservice here
        $userData = ['username' => 'city-online-demo', 'password' => 'sfhui2738fiuwe23', 'session' => '123213324', 'cnum' => 'demo'];
        // pretend it returns an array on success, false if there is no user

        if ($userData) {
            $password = $userData['password'];
            $session = $userData['session'];
            $cnum = $userData['cnum'];

            return new CityonlineUser($username, $password, $session, $cnum);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function loadUserBySession($session)
    {
        $session_data = $this->conn->fetchAssoc('SELECT * FROM freedom_cityonline.sessions WHERE session_id=:session',
        [
            'session' => $session
        ]);

        if ($session_data) {
            return new CityonlineUser($session_data['login'], $session_data['password'], $session, $session_data['cnum']);
        }

        throw new UsernameNotFoundException(
            sprintf('Сессия "%s" не найдена.', $session)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof CityonlineUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserBySession($user->getSession());
    }

    public function supportsClass($class)
    {
        return CityonlineUser::class === $class;
    }
}
?>
