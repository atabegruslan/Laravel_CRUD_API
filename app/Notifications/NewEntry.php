<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class NewEntry extends Notification
{
    use Queueable;

    protected $entry_url;
    protected $name;
    protected $entry_id;
    protected $img_url;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notice)
    {
        $this->entry_url = $notice['entry_url'];
        $this->name      = $notice['name'];
        $this->entry_id  = $notice['entry_id'];
        $this->img_url   = $notice['img_url'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'url'  => $this->entry_url,
            'name' => $this->name,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $data = [
            'entry_id'  => $this->entry_id,
            'entry_url' => $this->entry_url,
            'base_url'  => url('/'),
        ];

        return (new WebPushMessage)
            ->title('New Travel Blog entry!')
            ->icon(url('/images/sys/favicon-1.png'))
            ->body('A new Travel Blog entry about ' . $this->name . ' was added')
            ->data($data)
            ->dir('ltr')
            ->image($this->img_url)
            ->lang('en-US')
            ->tag($notification->id)
            ->action('View entry', 'view')
            ->action('No thanks', 'close')
            ->vibrate([100, 50, 100]);
            // ->renotify()
            // ->requireInteraction()
            // ->badge();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
