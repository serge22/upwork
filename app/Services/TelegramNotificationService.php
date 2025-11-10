<?php

namespace App\Services;

use App\Models\UpworkJob;
use App\Models\Feed;
use Illuminate\Support\Number;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramNotificationService
{
    /**
     * Send a job notification to a user via Telegram
     */
    public function sendJobNotification(string $telegramId, UpworkJob $job, Feed $feed): bool
    {
        $reply_markup = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => 'Open job',
                    'url' => 'https://www.upwork.com/jobs/' . $job->ciphertext,
                ])
            ]);

        $params = [
            'chat_id' => $telegramId,
            'text' => $this->formatJobMessage($job, $feed),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => $reply_markup,
        ];
        $response = Telegram::sendMessage($params);
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
        $description = strip_tags($job->description);

        // Calculate remaining space for description (4000 char limit total)
        $maxLength = 4000;
        $currentLength = mb_strlen($message) + mb_strlen("<blockquote expandable></blockquote>");
        $availableSpace = $maxLength - $currentLength;

        if (mb_strlen($description) > $availableSpace && $availableSpace > 0) {
            $description = mb_substr($description, 0, $availableSpace - 3) . '...';
        }

        $message .= "<blockquote expandable>" . $description . "</blockquote>";

        // Final safety check to ensure we don't exceed 4000 characters
        if (mb_strlen($message) > $maxLength) {
            $message = mb_substr($message, 0, $maxLength - 3) . '...';
        }

        return $message;
    }

    private function formatJobDetails(UpworkJob $job): string
    {
        $details = [];

        if ($job->category) {
            $details[] = $job->category->label;
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

        if ($job->client_spent) {
            $aboutClient[] = "Spent &#36;" . Number::abbreviate($job->client_spent);
        }

        if ($job->client_verified) {
            $aboutClient[] = "✅ Verified";
        }

        if ($aboutClient) {
            $out = "<b>About Client</b>\n";
            $out .= implode(" | ", $aboutClient);
        }

        return $out;
    }
}
