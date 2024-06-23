<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\UploadService;
use Carbon\Carbon;
use App\Models\File;
use App\Models\Config;

class  Helper
{
    public function getLoginedManager() {
        return $manager = Auth::guard('manager')->user();
    }

    public function getLoginedUser() {
        return $user = Auth::guard('user')->user();
    }

    public function changeFileNameUpload($file, $to = 'publisher') {
        $uuid = Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(7) . '-'. $uuid;
        $filename  = $filename. '.'.$extension;
        return $filename;
    }

    public function commonString($listStatus, $typeStatus, $isTrans = false) {
        if(isset($listStatus[$typeStatus])) {
            $title = $listStatus[$typeStatus]['title'] ?? $listStatus[$typeStatus];
            return $isTrans ? __($title) : $title;
        }
        return null;
    }

    public function formatPaginate($data) {
        $paginateData = [
            'data' => $data->items(),
            'total' => $data->total(),
            'per_page' => $data->count(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'has_more_pages' => $data->currentPage() == $data->lastPage() ? false : true,
        ];
        return $paginateData;
    }
}
