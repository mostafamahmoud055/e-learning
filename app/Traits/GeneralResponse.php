<?php

namespace App\Traits;

trait GeneralResponse
{

    public function returnError($message,$code)
    {
        return response()->json([
            'success' => false,
            'errors' => true,
            'message' => $message,
        ],$code);
    }

    public function returnSuccessMessage($message = "")
    {
        return [
            'success' => true,
            'errors' => false,
            'message' => $message
        ];
    }

    public function returnData($message, $data, $key ='data')
    {
        return response()->json([
            'success' => true,
            'errors' => false,
            'message' => $message,
            $key => $data,
        ],200);
    }
}
