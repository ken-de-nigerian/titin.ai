<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\UserCvStatus;
use App\Models\UserCv;
use App\Services\User\UserCvService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

final class ProcessUserCvJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userCvId,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function handle(UserCvService $userCvService): void
    {
        $userCv = UserCv::query()->with('user')->find($this->userCvId);
        if ($userCv === null || $userCv->user === null) {
            return;
        }

        $userCv = $userCvService->markStatus($userCv, UserCvStatus::Processing);

        $pythonUrl = (string) config('services.livekit.token_server_url');
        $sharedSecret = (string) config('services.livekit.internal_secret');
        if ($pythonUrl === '' || $sharedSecret === '') {
            $userCvService->markStatus($userCv, UserCvStatus::Failed);

            return;
        }

        $pythonAccessibleBaseUrl = (string) (
            config('services.livekit.python_accessible_app_url')
            ?: config('app.url')
        );
        $pythonAccessibleBaseUrl = rtrim($pythonAccessibleBaseUrl, '/');

        $parsedRoot = parse_url($pythonAccessibleBaseUrl);
        $scheme = isset($parsedRoot['scheme'])
            ? strtolower((string) $parsedRoot['scheme'])
            : 'https';

        URL::useOrigin($pythonAccessibleBaseUrl);
        URL::forceScheme($scheme);

        try {
            $resumeUrl = URL::temporarySignedRoute(
                'internal.cv.file',
                now()->addMinutes(15),
                ['cv' => $userCv->id],
            );
        } finally {
            URL::useOrigin(null);
            URL::forceScheme(null);
        }

        $callbackUrl = $pythonAccessibleBaseUrl.route('internal.cv.parsed', absolute: false);

        try {
            Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'X-Internal-Token' => $sharedSecret,
                ])
                ->connectTimeout(5)
                ->timeout(20)
                ->retry(2, 250)
                ->post(rtrim($pythonUrl, '/').'/internal/cv/parse', [
                    'user_id' => (int) $userCv->user_id,
                    'cv_id' => (int) $userCv->id,
                    'file_url' => $resumeUrl,
                    'file_name' => $userCv->client_original_name ?? $userCv->original_name,
                    'callback_url' => $callbackUrl,
                ])
                ->throw();
        } catch (RequestException) {
            $userCvService->markStatus($userCv, UserCvStatus::Failed);
        }
    }
}
