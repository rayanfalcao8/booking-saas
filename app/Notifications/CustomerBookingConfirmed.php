<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerBookingConfirmed extends Notification implements ShouldQueue
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
        $channels = ['mail'];

        if ($notifiable->routeNotificationFor('sms', $this) && config('services.sms.driver', 'off') !== 'off') {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toSms(object $notifiable): string
    {
        $bookingDate = $this->booking->date instanceof \DateTimeInterface
            ? $this->booking->date->format('Y-m-d')
            : (string) $this->booking->date;

        $confirmationUrl = route('public.booking.confirmation', [
            'business' => $this->booking->business?->slug,
            'booking' => $this->booking->id,
            'token' => $this->booking->cancellation_token,
        ]);

        return sprintf(
            'Reservix: rendez-vous confirmé chez %s le %s à %s. Détails: %s',
            $this->booking->business?->name ?? config('app.name'),
            $bookingDate,
            substr((string) $this->booking->start_time, 0, 5),
            $confirmationUrl,
        );
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $bookingDate = $this->booking->date instanceof \DateTimeInterface
            ? $this->booking->date->format('Y-m-d')
            : (string) $this->booking->date;

        $cancelUrl = null;

        if (is_string($this->booking->cancellation_token) && $this->booking->cancellation_token !== '') {
            $cancelUrl = route('public.booking.cancel', [
                'business' => $this->booking->business?->slug,
                'booking' => $this->booking->id,
                'token' => $this->booking->cancellation_token,
            ]);
        }

        $confirmationUrl = route('public.booking.confirmation', [
            'business' => $this->booking->business?->slug,
            'booking' => $this->booking->id,
            'token' => $this->booking->cancellation_token,
        ]);

        return (new MailMessage)
            ->subject('Confirmation de votre réservation')
            ->markdown('mail.bookings.customer-confirmed', [
                'booking' => $this->booking,
                'bookingDate' => $bookingDate,
                'serviceName' => $this->booking->service?->name ?? '-',
                'staffName' => $this->booking->staff?->name ?? '-',
                'businessName' => $this->booking->business?->name ?? config('app.name'),
                'confirmationUrl' => $confirmationUrl,
                'cancelUrl' => $cancelUrl,
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
