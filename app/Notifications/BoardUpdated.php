<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class BoardUpdated extends Notification
{
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Изменения на доске')
            ->body('На доске были обновлены задачи')
            ->icon('/icons/icon-192x192.png')
            ->badge('/icons/icon-192x192.png')
            ->data([
                'url' => url('/boards/' . $this->board->uuid),
                'type' => 'PUSH',
                'notificationType' => 'board_updated',
                'board_uuid' => $this->board->uuid,
            ])
            ->action('Открыть доску', 'open_board')
            ->vibrate([100, 50, 100]);
    }
}
