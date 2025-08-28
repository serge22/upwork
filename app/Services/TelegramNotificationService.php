<?php

namespace App\Services;

use App\Models\UpworkJob;
use App\Models\Feed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private string $botToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.client_secret');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a job notification to a user via Telegram
     */
    public function sendJobNotification(string $telegramId, UpworkJob $job, Feed $feed): bool
    {
        $message = $this->formatJobMessage($job, $feed);

        return $this->sendMessage($telegramId, $message);
    }

    /**
     * Send a message to a Telegram user
     */
    private function sendMessage(string $telegramId, string $message): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $telegramId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Failed to send Telegram message', [
                'telegram_id' => $telegramId,
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception sending Telegram message', [
                'telegram_id' => $telegramId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format the job information into a readable message
     */
    private function formatJobMessage(UpworkJob $job, Feed $feed): string
    {
        $message = "<b>{$job->title}</b>\n\n";

        $message .= $this->formatClientInfo($job);

        $message .= "\n<b>Description:</b>\n";
        $message .= "<blockquote expandable>" . strip_tags($job->description) . "</blockquote>\n\n";

        $message .= "\n🔗 <b>View Job:</b> https://www.upwork.com/jobs/{$job->ciphertext}";

        return $message;
    }

    private function formatClientInfo(UpworkJob $job): string
    {
        $out = '';
        $aboutClient = [];

        if ($job->client_feedback) {
            $aboutClient[] = "&#11088; " . floatval($job->client_feedback);
        }

        if ($job->client_reviews) {
            $aboutClient[] = intval($job->client_reviews) . " reviews";
        }

        if ($job->client_country) {
            $aboutClient[] = $job->client_country;
        }

        if ($job->client_jobs) {
            $aboutClient[] = intval($job->client_jobs) . " jobs";
        }

        if ($job->client_verified) {
            $aboutClient[] = "✅ Verified";
        }

        if ($job->client_spent) {
            $aboutClient[] = "Spent: &#36;" . intval($job->client_spent);
        }

        if ($aboutClient) {
            $out = "<b>About Client</b>\n";
            $out .= implode(" | ", $aboutClient) . "\n";
        }

        return $out;
    }
}
