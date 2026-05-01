<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\UserCv;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

final class CvFileDownloadController extends Controller
{
    public function __invoke(Request $request, UserCv $cv): Response
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($cv->path)) {
            abort(404);
        }

        $contents = $disk->get($cv->path);
        $downloadName = $cv->client_original_name ?? $cv->original_name;

        return response($contents, 200, [
            'Content-Type' => $cv->mime,
            'Content-Disposition' => 'attachment; filename="'.$downloadName.'"',
        ]);
    }
}
