<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\IndexAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        return $action->handle($request);
    }
}
