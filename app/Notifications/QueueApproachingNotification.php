<?php

namespace App\Notifications;

use App\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueApproachingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Queue $queueModel,
        public int $positionAhead = 5
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
        $checkStatusUrl = url('/antrian/status?number='.$queueNumber);

        return (new MailMessage)
            ->subject('Antrian Anda Hampir Dipanggil - '.$queueNumber)
            ->greeting('Halo, '.$this->queueModel->name.'!')
            ->line('Giliran Anda akan segera tiba!')
            ->line('**Nomor Antrian:** '.$queueNumber)
            ->line('**Layanan:** '.$serviceName)
            ->line('**Posisi:** '.$this->positionAhead.' antrian lagi')
            ->action('Cek Status Antrian', $checkStatusUrl)
            ->line('Harap bersiap dan pastikan Anda berada di lokasi.')
            ->salutation('Salam, Pengadilan Agama Penajam');
    }

    public function toFonnte(object $notifiable): string
    {
        $serviceName = $this->queueModel->service->name;
        $queueNumber = $this->queueModel->number;

        $message = "*PENGADILAN AGAMA PENAJAM*\n\n";
        $message .= "⏰ *Giliran Anda Hampir Tiba!*\n\n";
        $message .= "Halo, {$this->queueModel->name}\n\n";
        $message .= "📋 *Nomor Antrian:* {$queueNumber}\n";
        $message .= "🏢 *Layanan:* {$serviceName}\n";
        $message .= "📍 *Posisi:* {$this->positionAhead} antrian lagi\n\n";
        $message .= "⚠️ Harap bersiap dan pastikan Anda berada di lokasi.\n\n";
        $message .= "Cek status: ".url('/antrian/status?number='.$queueNumber);

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
            'position_ahead' => $this->positionAhead,
            'type' => 'queue_approaching',
        ];
    }
}
