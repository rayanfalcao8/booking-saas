<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerBookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking) {}

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

        $message = (new MailMessage)
            ->subject('Confirmation de votre booking')
            ->greeting('Bonjour '.$this->booking->customer_name.',')
            ->line('Votre booking est confirme.')
            ->line('Service: '.($this->booking->service?->name ?? '-'))
            ->line('Date: '.$bookingDate)
            ->line('Heure: '.$this->booking->start_time.' - '.$this->booking->end_time);

        if (is_string($this->booking->cancellation_token) && $this->booking->cancellation_token !== '') {
            $cancelUrl = route('public.booking.cancel', [
                'business' => $this->booking->business?->slug,
                'booking' => $this->booking->id,
                'token' => $this->booking->cancellation_token,
            ]);

            $message->line('Besoin d’annuler ? Utilisez le lien ci-dessous avant l’heure du rendez-vous.')
                ->action('Annuler ma réservation', $cancelUrl);
        }

        return $message->line('Merci pour votre confiance.');
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
