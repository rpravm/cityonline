<?php
// src/Controller/CityOnlineController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class CityOnlineController extends Controller
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route(
     *  "/{city}/login",
     *  defaults={"city": "krd"},
     *  requirements={"city": "mgn|krd|sib"}
     * )
     */
    public function login($city)
    {
        $this->session->set('myvar', '12321321');

        return $this->render('cityonline/login.twig', 
        [
            'city' => $city,
            'session' => var_export($this->session->get('myvar'), 1)
        ]);

//        return new Response("City: $_city");
    }
}

?>
