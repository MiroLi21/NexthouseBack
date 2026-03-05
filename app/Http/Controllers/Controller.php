<?php

namespace App\Http\Controllers;


abstract class Controller
{
    public function ok($data)
    {
        return response()->json([
            'message' => 'Successful',
            'length' => is_array($data) ? count($data) : 0,
            'data' => $data,
        ], status: 200);
    }
    public function SuccessfullyResponse($data, $message = 'Operations Successfully')
    {
        return response()->json(
            [
                'success' => true,
                'data' => $data,
                'message' => $message,
            ],
            200
        );
    }
    public function SuccessfullyResponsePagination($data, $message = 'Operations Successfully')
    {

        return response()->json(
            [
                'success' => true,
                'data' => $data->appends(['success' => true]),
                'message' => $message,
            ],
            200
        );
    }
    public function FailedResponse($message = 'Operations Unsuccessfully!!!', $code = 500)
    {
        return response()->json(
            [
                'success' => false,
                'data' => [],
                'message' => $message == 'Operations Unsuccessfully!!!' ? __('general.unsuccessfully') : $message
            ],
            $code
        );
    }
    public function ok_paginate($data)
    {
        // return $data;
        return response()->json([
            'message' => 'Successful',
            'data' => $data,
            'links' => [
                'self' => $data->resource->url($data->resource->currentPage()),
                'first' => $data->resource->url(1),
                'last' => $data->resource->url($data->resource->lastPage()),
                'prev' => $data->resource->previousPageUrl(),
                'next' => $data->resource->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $data->resource->currentPage(),
                'from' => $data->resource->firstItem(),
                'to' => $data->resource->lastItem(),
                'per_page' => $data->resource->perPage(),
                'total' => $data->resource->total(),
                'last_page' => $data->resource->lastPage(),
            ],
        ], status: 200);
    }
    public function ok2_paginate($data)
    {
        // return $data;
        return response()->json([
            'message' => 'Successful',
            'data' => $data->appends(['success' => true]),
        ], status: 200);
    }



    public function error($message = '', $status = 500)
    {
        return response()->json([
            'message' => $message,
            'data' => '',
        ], status: $status);
    }
}
