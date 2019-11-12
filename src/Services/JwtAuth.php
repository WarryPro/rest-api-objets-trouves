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

    public function signup($email, $password, $gettoken = null) {
        // 1. user exist
        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'password' => $password
        ]);

        $signup = false;
        if(is_object($user)) {
            $signup = true;
        }
        // 2. if user exist, generate token jwt
        if($signup) {
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
                $data = $jwt;
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
}