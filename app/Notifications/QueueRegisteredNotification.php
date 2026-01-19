<?php

namespace App\Notifications;

use App\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Queue $queueModel
    ) {}

    /**
     * Get the notification's delivery channels.
     *
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $serviceName = $this->queueModel->service->name;
        $queueNumber = $this->queueModel->number;
        $checkStatusUrl = url('/antrian/status?number='.$queueNumber);

        return (new MailMessage)
            ->subject('Pendaftaran Antrian Berhasil - '.$queueNumber)
            ->greeting('Halo, '.$this->queueModel->name.'!')
            ->line('Pendaftaran antrian Anda telah berhasil.')
            ->line('**Nomor Antrian:** '.$queueNumber)
            ->line('**Layanan:** '.$serviceName)
            ->line('**Tanggal:** '.now()->translatedFormat('l, d F Y'))
            ->action('Cek Status Antrian', $checkStatusUrl)
            ->line('Harap simpan nomor antrian ini dan datang sesuai jadwal.')
            ->line('Anda akan menerima notifikasi ketika giliran Anda hampir tiba.')
            ->salutation('Salam, Pengadilan Agama Penajam');
    }

    public function toFonnte(object $notifiable): string
    {
        $serviceName = $this->queueModel->service->name;
        $queueNumber = $this->queueModel->number;
        $estimatedTime = $this->queueModel->estimated_time;

        $message = "*PENGADILAN AGAMA PENAJAM*\n\n";
        $message .= "✅ *Pendaftaran Berhasil*\n\n";
        $message .= "Halo, {$this->queueModel->name}\n\n";
        $message .= "📋 *Nomor Antrian:* {$queueNumber}\n";
        $message .= "🏢 *Layanan:* {$serviceName}\n";
        $message .= "📅 *Tanggal:* ".now()->translatedFormat('l, d F Y')."\n";

        if ($estimatedTime) {
            $message .= "⏰ *Estimasi Tunggu:* ~{$estimatedTime} menit\n";
        }

        $message .= "\nSimpan nomor antrian ini dan datang sesuai jadwal.\n";
        $message .= "Anda akan menerima notifikasi ketika giliran hampir tiba.\n\n";
        $message .= "Cek status: ".url('/antrian/status?number='.$queueNumber);

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'queue_id' => $this->queueModel->id,
            'queue_number' => $this->queueModel->number,
            'service_name' => $this->queueModel->service->name,
            'type' => 'queue_registered',
        ];
    }
}
