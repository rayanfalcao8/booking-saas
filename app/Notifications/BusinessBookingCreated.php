<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessBookingCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Booking $booking) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

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
        $bookingDate = $this->booking->date instanceof \DateTimeInterface
            ? $this->booking->date->format('Y-m-d')
            : (string) $this->booking->date;

        return (new MailMessage)
            ->subject('Nouvelle réservation confirmée')
            ->markdown('mail.bookings.business-created', [
                'booking' => $this->booking,
                'bookingDate' => $bookingDate,
                'serviceName' => $this->booking->service?->name ?? '-',
                'staffName' => $this->booking->staff?->name ?? '-',
                'businessName' => $this->booking->business?->name ?? config('app.name'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
