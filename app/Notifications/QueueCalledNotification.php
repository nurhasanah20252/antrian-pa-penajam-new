<?php

namespace App\Notifications;

use App\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueCalledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Queue $queueModel,
        public string $counterName = ''
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->shouldNotifyByEmail()) {
            $channels[] = 'mail';
        }

        if ($notifiable->shouldNotifyBySms() && config('services.fonnte.api_key')) {
            $channels[] = \App\Notifications\Channels\FonnteChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $serviceName = $this->queueModel->service->name;
        $queueNumber = $this->queueModel->number;
        $counterInfo = $this->counterName ? ' di '.$this->counterName : '';

        return (new MailMessage)
            ->subject('GILIRAN ANDA SEKARANG - '.$queueNumber)
            ->greeting('Halo, '.$this->queueModel->name.'!')
            ->line('**GILIRAN ANDA SEKARANG!**')
            ->line('**Nomor Antrian:** '.$queueNumber)
            ->line('**Layanan:** '.$serviceName)
            ->line('Silakan menuju loket'.$counterInfo.' segera.')
            ->line('Harap segera datang dalam waktu 5 menit.')
            ->salutation('Salam, Pengadilan Agama Penajam');
    }

    public function toFonnte(object $notifiable): string
    {
        $serviceName = $this->queueModel->service->name;
        $queueNumber = $this->queueModel->number;
        $counterInfo = $this->counterName ? " di {$this->counterName}" : '';

        $message = "*PENGADILAN AGAMA PENAJAM*\n\n";
        $message .= "🔔 *GILIRAN ANDA SEKARANG!*\n\n";
        $message .= "Halo, {$this->queueModel->name}\n\n";
        $message .= "📋 *Nomor Antrian:* {$queueNumber}\n";
        $message .= "🏢 *Layanan:* {$serviceName}\n";
        $message .= "📍 Silakan menuju loket{$counterInfo} *SEGERA*\n\n";
        $message .= "⏰ Harap datang dalam waktu 5 menit.";

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'queue_id' => $this->queueModel->id,
            'queue_number' => $this->queueModel->number,
            'service_name' => $this->queueModel->service->name,
            'counter_name' => $this->counterName,
            'type' => 'queue_called',
        ];
    }
}
