<?php

namespace App\Services;

use App\Models\UpworkJob;
use App\Models\Feed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TelegramNotificationService
{
    protected Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api();
    }

    /**
     * Send a job notification to a user via Telegram
     */
    public function sendJobNotification(string $telegramId, UpworkJob $job, Feed $feed): bool
    {
        $message = $this->formatJobMessage($job, $feed);

        $response = $this->telegram->sendMessage(['chat_id' => $telegramId, 'text' => $message, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
        return $response->getMessageId() !== null;
    }

    /**
     * Format the job information into a readable message
     */
    private function formatJobMessage(UpworkJob $job, Feed $feed): string
    {
        $message = "<b>{$job->title}</b>\n\n";

        $message .= $this->formatJobDetails($job) . "\n\n";
        $message .= $this->formatClientInfo($job) . "\n\n";

        $message .= "<b>Description:</b>\n";
        $message .= "<blockquote expandable>" . strip_tags($job->description) . "</blockquote>\n\n";

        $message .= "\n🔗 <b>View Job:</b> https://www.upwork.com/jobs/{$job->ciphertext}";

        return $message;
    }

    private function formatJobDetails(UpworkJob $job): string
    {
        $details = [];

        if ($job->subcategoryModel) {
            $details[] = $job->subcategoryModel->label;
        }

        if ($job->amount) {
            $details[] = 'Fixed-price';
            $details[] = "<b>\${$job->amount}</b>";
        }

        if ($job->hourlyBudgetType) {
            $details[] = 'Hourly';
        }

        if ($job->hourlyBudgetMax) {
            $details[] = sprintf('<b>$%d-$%d per hour</b>', $job->hourlyBudgetMin, $job->hourlyBudgetMax);
        }

        if ($job->engagement) {
            $details[] = $job->engagement;
        }

        if ($job->experience) {
            $details[] = $job->experience;
        }

        if ($job->premium) {
            $details[] = "💎 Featured";
        }

        return implode(" | ", $details);
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
            $out .= implode(" | ", $aboutClient);
        }

        return $out;
    }
}
