<?php

namespace App\Http\Controllers\Guest;

use App\Utils\Dict;
use App\Http\Controllers\Controller;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'tos_url' => config('v2board.tos_url'),
                'app_name' => config('v2board.app_name'),
                'app_description' => config('v2board.app_description'),
            ]
        ]);
    }
}
