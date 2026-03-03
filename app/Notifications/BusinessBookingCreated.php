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

        return (new MailMessage)
            ->subject('Nouveau booking confirme')
            ->line('Un nouveau booking a ete cree.')
            ->line('Client: '.$this->booking->customer_name)
            ->line('Service: '.($this->booking->service?->name ?? '-'))
            ->line('Employe: '.($this->booking->staff?->name ?? '-'))
            ->line('Date: '.$bookingDate)
            ->line('Heure: '.$this->booking->start_time.' - '.$this->booking->end_time);
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
