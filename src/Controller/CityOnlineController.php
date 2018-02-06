<?php
// src/Controller/CityOnlineController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Doctrine\DBAL\Driver\Connection;
use Psr\Log\LoggerInterface;

use App\Freedom\WS1C;

class CityOnlineController extends Controller
{

//    private $session;
    private $conn;
    private $logger;
    private $ws1c;

    private $cities = ['mgn' => 'Магнитогорск', 'sib' => 'Сибай', 'krd' => 'Краснодар'];

    private $defaults =
    [
        'demoLogin' => "city-online-demo",
        'demoPassword' => "sfhui2738fiuwe23",
        'demoMessage' =>
        "Демо включает в себя пример двора и камеры в нескольких населенных пунктах.\n\n
Для получения доступа к камерам вашего двора, введите логин и пароль от Личного Кабинета
интернет-провайдера «Фридом».\n\nПодключиться к\nинтернет-провайдеру «Фридом» можно\nпо тел. +7 (800) 333-88-13"
    ];

    private $connectionRequest = [
        'message' =>
        "Подключите интернет, телевидение и другие наши услуги, заполнив
простую форму ниже. Обязательно укажите свой номер телефона для связи.\n\n
Наши операторы свяжутся с вами по указанному телефону в ближайшее время.",
        'services' => [
            ['id' => 1, 'name' => 'Интернет'],
            ['id' => 2, 'name' => 'Кабельное ТВ']
        ]
    ];

    private $isp = [
        'city' => 'Краснодар',
        'name' => 'Фридом'
    ];

    private $interface = [
        'hideCams' => false,
        'hideRoads' => false,
        'hideNews' => true,
        'hideAccountDetails' => false,
        'camsLabel' => 'Персональные камеры',
        'roadsLabel' => 'Публичные камеры'
    ];

    private $urls = [
        'crossingCheck',
        'camCheck',
        'camsList',
        'roadsList',
        'newsList',
        'openDoor',
        'dvrMap',
        'connectionRequest',
        'accountDetails'
    ];


    public function __construct(Connection $conn, LoggerInterface $logger, WS1C $ws1c)
    {
//        $this->session = $session;
        $this->conn = $conn;
        $this->logger = $logger;
        $this->ws1c = $ws1c;

    }

    /**
     * @Route(
     *  "/",
     *  name="homepage",
     * )
     */
    public function home()
    {
        return new Response('cityonline');
    }

    /**
     * @Route(
     *  "/{city}/roads",
     *  name="roadsList",
     *  requirements={"city": "mgn|krd|sib"}
     * )
     * @Route( "/roads", defaults={"city": "krd"} )
     */
    public function roads($city)
    {
        return $this->camslist($city, true);
    }

    /**
     * @Route(
     *  "/{city}/cams",
     *  name="camsList",
     *  requirements={"city": "mgn|krd|sib"}
     * )
     * @Route( "/cams", defaults={"city": "krd"} )
     */
    public function camslist($city, $roads = false)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        if($this->getUser()->getCnum() == 'demo') {
            $cams_res = $this->conn->fetchAll('SELECT * FROM freedom_cityonline.cams WHERE demo');
        } else {
            // ДОРОГИ
            if($roads) {
                $cams_res = $this->conn->fetchAll('SELECT * FROM freedom_cityonline.cams WHERE type=\'road\' AND city=:city', ['city' => $this->session['city']]);
            // ТВОЙ ДВОР
            } else {
                $cams_res = $this->conn->fetchAll('SELECT * FROM freedom_cityonline.cams WHERE type=\'private\' AND INSTR (contracts, :cnum)' , ['cnum' => $this->session['cnum']]);
            }
        }

        foreach($cams_res as $cam) {
            $sources = [];
            if($cam['source-sd']) {
                $s['name'] = $cam['source-sd'];
                $s['host'] = $cam['host-sd'];
                $sources['sd'] = $s;
            }
            if($cam['source-hd']) {
                $s['name'] = $cam['source-hd'];
                $s['host'] = $cam['host-hd'];
                $sources['hd'] = $s;
            }
            $cam_arr['id'] = $cam['id'];
            if($sources) {
                $cam_arr['sources'] = $sources;
            }
            $cam_arr['description'] = $cam['description'];
            $cam_arr['street'] = $cam['street'];
            $cam_arr['building'] = $cam['building'];
            $cam_arr['name'] = $cam['name'];
            $cam_arr['host'] = $cam['host'];

            if($roads) {
                $cam_arr['district'] = $cam['city'];
            }

            $cams[] = $cam_arr;
        }
        if($roads) {
            $ret['roads'] = $cams;
        } else {
            $ret['cams'] = $cams;
        }

        return $this->json($ret);
    }

    /**
     * @Route(
     *  "/{city}/account_details",
     *  name="accountDetails",
     *  requirements={"city": "mgn|krd|sib"}
     * )
     */
    public function account_details($city)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
        if($this->getUser()->getCnum() == 'demo') {
            return $app->json([]);
        }

        $login = $this->getUser()->getUsername();

        if($contract_ws = $this->ws1c->call_ws_method('get_contract', ['search' => ['type' => 'login', 'value' => $login], 'fields' => ['all']])) {
            if(!$contract_ws['error']) {
                $contract = $contract_ws['result'];
            } else {
                $ret['error'] = $contract_ws['error'];
                return $this->json($ret);
            }
        }

        $ret['balance'] = $contract['balance'];
        $ret['basicAccount'] = $contract['contractNum'];
        unset($ret['req_data']);

        return $this->json($ret);
    }

    /**
     * @Route(
     *  "/{city}/login",
     *  defaults={"city": "krd"},
     *  requirements={"city": "mgn|krd|sib"}
     * )
     */
    public function login(Request $request, $city)
    {
        $req_data = json_decode($request->getContent(), true);

        if(!$req_data) {
            return $this->json(['error' => 'Invalid JSON request']);
        }

        $password_ok = false;

        // Демо доступ
        if($req_data['login'] == $this->defaults['demoLogin'] && $req_data['password'] == $this->defaults['demoPassword']) {
            $demo = 1;
            $password_ok = true;
        // Доступ по договору
        } else {
            $demo = 0;
            $contract_ws = $this->ws1c->call_ws_method('get_contract', ['search' => ['type' => 'login', 'value' => $req_data['login']], 'fields' => ['all']]);
            if($contract_ws['error']) {
                return $this->json(['error' => '1C error: ' . $contract_ws['error']]);
            }
        }

        $contract = $contract_ws['result'];

        foreach($contract['logins'] as $login) {
            if($login['login'] == $req_data['login'] && $login['password'] == $req_data['password']) {
                $password_ok = true;
            }
        }

        if(!$password_ok) {
            return $this->json(['error' => 'wrong password']);
        }

        $rand_bytes = random_bytes(16);
        $session_id = bin2hex($rand_bytes);

        if($demo) {
            $user_city = $cnum = 'demo';
        } else {
            $user_city = $contract['address']['city'];
            $cnum = $contract['contractNum'];
        }

        $this->conn->executeQuery('INSERT INTO freedom_cityonline.sessions SET session_id=:session_id, dt=NOW(), device=:device, login=:login, password=:password, demo=:demo, city=:city, user_city=:user_city, cnum=:cnum',
        [
            'session_id' => $session_id,
            'device' => json_encode($req_data['device']),
            'login' => $req_data['login'],
            'password' => $req_data['password'],
            'demo' => $demo,
            'city' => $city,
            'user_city' => $user_city,
            'cnum' => $cnum
        ]);

        return $this->json(['session' => $session_id]);
    }

    /**
     * @Route(
     *  "/{city}/config",
     *  requirements={"city": "mgn|krd|sib"}
     * )
     */
    public function config($city)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        foreach($this->urls as $url) {
            dump($url);
            try {
                $ret['urls'][$url] = $this->generateUrl($url, ['city' => $city], UrlGeneratorInterface::ABSOLUTE_URL);
            } catch(RouteNotFoundException $e) {
            }
        }

        $ret['connectionRequest'] = $this->connectionRequest;
        $ret['isp'] = $this->isp;
        $ret['interface'] = $this->interface;

        dump($this->getUser());

        if($this->getUser()->getCnum() == 'demo') {
            $ret['interface']['hideAccountDetails'] = true;
        }

        return $this->json($ret);
    }
}

?>
