<?php
// src/Security/SessionAuthenticator.php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Psr\Log\LoggerInterface;

class SessionAuthenticator extends AbstractGuardAuthenticator
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        $req_data = json_decode($request->getContent(), true);
        return isset($req_data['session']);
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $this->logger->info('getCredentials(): ');

        return(json_decode($request->getContent(), true));
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $session = $credentials['session'];

        if (null === $session) {
            return;
        }

        $this->logger->info('getUser(): ' . $session);
        return $userProvider->loadUserBySession($session);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $this->logger->info(var_export($credentials, 1));

        $signature = $credentials['session'] . '|' . $credentials['timestamp'] . '|' . $credentials['random'] . '|' . json_encode($credentials['request'], JSON_FORCE_OBJECT) . '|' . $user->getPassword();

        if(md5($signature) == $credentials['signature']) {
            return true;
        } else {
            $this->logger->error('Неверная подпись запроса: ' . $credentials['signature'] . ' != ' . md5($signature));
            return false;
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'error' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $req_data = json_decode($request->getContent(), true);

        $data = array(
            'error' => 'Auth needed'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
?>
