<?php
/**
 * Created by PhpStorm.
 * User: danny
 * Date: 17/11/2019
 * Time: 23:21
 */

namespace App\Services;

class Responses
{
    private $status;
    public $code;

    public function __construct()
    {

        $this->status = 'error';
        $this->code = 404;

    }

    public function error(string $message = null, int $code = null)
    {
        $this->code = ($code) ? $code : $this->code;
        if($message) {
            $data = [
                'status'    => $this->status,
                'code'      => $this->code,
                'message'   => $message,
            ];
        }
        else {
            $data = [
                'status'    => $this->status,
                'code'      => $this->code,
            ];
        }

        return $data;
    }

    public function success( object $json, string $message = null, int $code = null)
    {
        $this->status = 'success';
        $this->code = ($code) ? $code : 200;
        if($message) {
            $data = [
                'status'    => $this->status,
                'code'      => $this->code,
                'message'   => $message,
                'data'      => $json
            ];
        }
        else {
            $data = [
                'status'    => $this->status,
                'code'      => $this->code,
                'data'      => $json
            ];
        }

        return $data;
    }
}