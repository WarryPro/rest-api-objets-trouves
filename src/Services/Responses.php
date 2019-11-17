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
    private $code;

    public function __construct()
    {

        $this->status = 'error';
        $this->code = 404;

    }

    public function error($message = null)
    {
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

    public function success( $json, $message = null)
    {
        $this->status = 'success';
        $this->code = 200;

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