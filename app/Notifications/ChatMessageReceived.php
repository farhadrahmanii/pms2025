<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class ChatMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private $sender;
    private $message;
    private $chatUrl;

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
        $this->sender = $sender;
        $this->message = $message;
        $this->chatUrl = $chatUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line(
                __('You have received a new chat message from :name.', [
                    'name' => $this->sender->name
                ])
            )
            ->line(__('Message: ":message"', ['message' => $this->message]))
            ->action(
                __('Open Chat'),
                $this->chatUrl
            );
    }

    public function toDatabase(User $notifiable): array
    {
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
            ->title(
                __('New chat message from :name', [
                    'name' => $this->sender->name
                ])
            )
            ->icon('heroicon-o-chat')
            ->body(fn() => __('Message: ":message"', ['message' => json_decode($messageText, true)['body']]))
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-chat')
                    ->url(fn() => $this->chatUrl),
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
