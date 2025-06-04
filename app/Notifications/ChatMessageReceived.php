<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ChatMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private $senderId;
    private $message;

    /**
     * Create a new notification instance.
     *
     * @param User $sender
     * @param string $message
     * @param string $chatUrl
     * @return void
     */
    public function __construct(User $sender, string $message, string $chatUrl)
    {
        $this->senderId = $sender->getKey();
        $this->message = $message;
        // $chatUrl is ignored, always generate from notifiable in notification
    }

    private function getSender()
    {
        return \App\Models\User::find($this->senderId);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', WebPushChannel::class];
    }
    public function toWebPush($notifiable, $notification)
    {
        $sender = $this->getSender();
        $messageText = is_string($this->message) ? $this->message : '';
        return (new WebPushMessage)
            ->title('New chat message from ' . ($sender ? $sender->name : 'Unknown'))
            ->icon('/favicon.ico')
            ->body('Message: "' . $messageText . '"')
            ->options(['TTL' => 1000]);
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $sender = $this->getSender();
        return (new MailMessage)
            ->line('You have received a new chat message from ' . ($sender ? $sender->name : 'Unknown') . '.')
            ->line('Message: "' . $this->message . '"')
            ->action('Open Chat', url('chatify/' . $notifiable->id));
    }

    public function toDatabase($notifiable): array
    {
        $sender = $this->getSender();
        $messageText = $this->message;
        $decoded = null;
        if (is_string($messageText) && $this->isJson($messageText)) {
            $decoded = json_decode($messageText, true);
        }
        if (is_array($decoded) && isset($decoded['body'])) {
            $messageText = $decoded['body'];
        } elseif (is_array($messageText) && isset($messageText['body'])) {
            $messageText = $messageText['body'];
        } elseif (is_object($messageText) && isset($messageText->body)) {
            $messageText = $messageText->body;
        }
        return FilamentNotification::make()
            ->title('New chat message from ' . ($sender ? $sender->name : 'Unknown'))
            ->icon('heroicon-o-chat')
            ->body(fn() => 'Message: "' . (is_string($messageText) ? $messageText : '') . '"')
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-chat')
                    ->url(fn() => url('chatify/' . $notifiable->id)),
            ])
            ->getDatabaseMessage();
    }

    private function isJson($string): bool
    {
        if (!is_string($string))
            return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
