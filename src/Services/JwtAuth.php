<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 10/11/2019
 * Time: 13:36
 */

namespace App\Services;


use Firebase\JWT\JWT;
use App\Entity\User;
use Doctrine\ORM\EntityManager;


class JwtAuth
{
    public $manager;
    public $key;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
        $this->key = 'items_found_app';
    }

    public function login($email, $password, $gettoken = null) {
        // 1. user exist
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'password' => $password
        ]);

        $login = false;
        if(is_object($user)) {
            $login = true;
        }
        // 2. if user exist, generate token jwt
        if($login) {
            $token = [
                'sub'        => $user->getId(),
                'firstname'  => $user->getFirstname(),
                'lastname'   => $user->getLastname(),
                'email'      => $user->getEmail(),
                'role'       => $user->getRole(),
                'avatar'     => $user->getAvatar(),
                'iat'        => time(), // temps où le token est créé
                'expiration' => time() + (7 * 24 * 60 * 60), // une semaine pour expirer le token
            ];

            // 3. verify flag gettoken, condiction
            $jwt = JWT::encode($token, $this->key, 'HS256'); // Generate token
            if(!empty($gettoken)) {
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Maintenant vous êtes connecté',
                    'data' => JWT::decode($jwt, $this->key, ['HS256']),
                    'token' => $jwt];
            }else {
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
                $data = $decoded;
            }
        }else {
            $data = [
                'status' => 'error',
                'message' => 'Erreur de connexion'
            ];
        }

        // 4. return data
        return $data;
    }

    /**
     * @return boolean|object
     */
    public function checkToken($jwt, $identity = false)
    {
        $auth = false;

        try {
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        }catch (\UnexpectedValueException $exception){
            $auth = false;
        }catch (\DomainException $exception) {
            $auth = false;
        }



        if(isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        }else {
            $auth = false;
        }

        if($identity) {
            return $decoded;
        }
        else {

        return $auth;
        }
    }
}