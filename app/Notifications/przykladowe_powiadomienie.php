<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\ExpoPushNotifications\ExpoChannel;
use NotificationChannels\ExpoPushNotifications\ExpoMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $body;
    public $user_name;
    public $secret_notification;

    public function __construct($message, $secret_notification)
    {
        $this->body = $message->text;
        $this->user_name = $message->user->name;
        $this->secret_notification = $secret_notification;
    }

    public function via($notifiable)
    {
        return [ExpoChannel::class];
    }

    public function toExpoPush($notifiable)
    {
        if($this->secret_notification === false) {
            return ExpoMessage::create()
                ->badge(1)
                ->enableSound()
                ->title($this->user_name)
                ->setChannelId('chat-messages')
                //->ttl(60)
                ->body($this->body);
        } else {
            return ExpoMessage::create()
                ->badge(1)
                ->enableSound()
                ->title($this->user_name)
                ->setChannelId('chat-messages')
                //->ttl(60)
                ->body('Masz nową wiadomość!');
        }
        
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}