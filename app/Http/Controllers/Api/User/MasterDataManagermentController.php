<?php

namespace App\Http\Controllers\Api\Management;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Helpers\LogHelper;
use App\Models\Dmhuyen;
use App\Models\Dmtinh;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MasterDataManagermentController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $requestData = $request->all();
            $listType = $requestData['list_type'] ?? [];
            $searchParams = $requestData['search_params'] ?? [];

            $data = [];
            if (isset($listType['dmtinhs']) && $listType['dmtinhs'] == 1) {
                $dmtinhs = (new Dmtinh())->select('id', 'matinh', 'tentinh')->orderBy('id', "ASC")->get();
                $data['dmtinhs'] = $dmtinhs;
            }
            if (isset($listType['dmhuyens']) && $listType['dmhuyens'] == 1) {
                $dmhuyens = (new Dmhuyen())->select('id', 'matinh', 'mahuyen', 'tenhuyen')->orderBy('id', "ASC")->get();
                $data['dmhuyens'] = $dmhuyens;
            }

            return $this->responseSuccess($data);
        } catch (\Exception $e) {
            LogHelper::error('CuahangManagement.index failed', $e);
            return $this->responseErrorMessage(trans('messages.error_server'), 500);
        }
    }
}
