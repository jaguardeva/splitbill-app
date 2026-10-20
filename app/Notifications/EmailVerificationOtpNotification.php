<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationOtpNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $otpCode,
        public int $expiryMinutes = 10
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name = isset($notifiable->name) ? (string) $notifiable->name : 'Pengguna';

        return (new MailMessage)
            ->subject('Kode Verifikasi Email Anda - Split Bill')
            ->greeting('Halo, '.$name.'!')
            ->line('Terima kasih telah mendaftar di Split Bill Calculator.')
            ->line('Gunakan kode One-Time Password (OTP) berikut untuk memverifikasi alamat email Anda:')
            ->line("# {$this->otpCode}")
            ->line("Kode ini hanya berlaku selama {$this->expiryMinutes} menit. Jangan berikan kode ini kepada siapa pun.")
            ->line('Jika Anda tidak merasa mendaftar di Split Bill Calculator, Anda dapat mengabaikan email ini.');
    }
}
