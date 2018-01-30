<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LuckyController extends Controller
{
    /**
     * @Route("/lucky/number/{max}", name="app_lucky_number")
     */
    public function number($max = 200)
    {
        $number = mt_rand(0, $max);

        return $this->render('lucky/number.html.twig', ['number' => $number]);

/*
        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
*/
    }
}
?>
