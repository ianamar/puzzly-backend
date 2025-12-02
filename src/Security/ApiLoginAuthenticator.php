<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Authenticator para login API JSON.
 * - Espera POST /login con body JSON: { "username": "...", "password": "..." }
 * - Devuelve JSON en success/failure y crea la sesión (cookie) si las credenciales son válidas.
 */
class ApiLoginAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        //solo acepta json
        $contentType = $request->headers->get('Content-Type') ?? '';
        if (strpos($contentType, 'application/json') === false) {
            throw new CustomUserMessageAuthenticationException('Expected JSON request.');
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new CustomUserMessageAuthenticationException('Invalid JSON.');
        }

        $username = isset($data['username']) ? trim((string)$data['username']) : '';
        $password = isset($data['password']) ? (string)$data['password'] : '';

        if ($username === '' || $password === '') {
            throw new CustomUserMessageAuthenticationException('username and password are required.');
        }

        //UserBadge carga el usuario usando entity user provider
        //PasswordCredentials verifica la contraseña
        $passport = new Passport(
            new UserBadge($username),
            new PasswordCredentials($password)
        );

        $passport->addBadge(new PasswordUpgradeBadge($password));

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //devuelve JSON y deja que la sesión exista via cookie
        $user = $token->getUser();
        $payload = [
            'status' => 'ok',
            'message' => 'Autenticación correcta',
            'user' => is_object($user) && method_exists($user, 'getUsername') ? ['username' => $user->getUsername()] : null,
        ];

        return new JsonResponse($payload, Response::HTTP_OK);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Si un recurso protegido es accedido por un usuario anónimo, Symfony llamará a este método.
     * Devolvemos JSON 401 para APIs.
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $data = [
            'message' => 'Authentication required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
