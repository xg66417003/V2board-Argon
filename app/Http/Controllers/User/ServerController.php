<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ServerService;
use App\Services\UserService;
use App\Utils\CacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Server;
use App\Models\ServerLog;
use App\Models\User;

use App\Utils\Helper;

class ServerController extends Controller
{
    public function fetch(Request $request)
    {
        $user = User::find($request->session()->get('id'));
        $servers = [];
        $userService = new UserService();
        if ($userService->isAvailable($user)) {
            $serverService = new ServerService();
            $servers = $serverService->getAvailableServers($user);
        }
        return response([
            'data' => $servers
        ]);
    }

    public function logFetch(Request $request)
    {
        $type = $request->input('type') ? $request->input('type') : 0;
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $serverLogModel = ServerLog::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC');
        switch ($type) {
            case 0:
                $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')));
                break;
            case 1:
                $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')) - 604800);
                break;
            case 2:
                $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-1')));
        }
        $total = $serverLogModel->count();
        $res = $serverLogModel->forPage($current, $pageSize)
            ->get();
        return response([
            'data' => $res,
            'total' => $total
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logFetchSeven(Request $request)
    {
        $serverLog = ServerLog::query()
            ->where('user_id', $request->session()->get('id'))
            ->selectRaw("FROM_UNIXTIME(created_at,'%m-%d') as date_time, SUM(u + d) as ud")
            ->where('created_at', '>=', strtotime(date('Y-m-d')) - 604800)
            ->groupBy('date_time')
            ->orderBy('date_time')
            ->get()
            ->toArray();

        return response()->json(['data' => $serverLog]);
    }
}
