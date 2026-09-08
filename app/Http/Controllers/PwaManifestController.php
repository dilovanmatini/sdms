<?php

namespace App\Http\Controllers;

use App\Actions\Pwa\ManifestAction;
use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(ManifestAction $action): JsonResponse
    {
        return $action->handle();
    }
}
