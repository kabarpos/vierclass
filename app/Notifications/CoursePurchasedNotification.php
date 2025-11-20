<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Transaction;
use App\Mail\CoursePurchaseConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoursePurchasedNotification extends Notification
{
    use Queueable;

    public $course;
    public $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Course $course, Transaction $transaction)
    {
        $this->course = $course;
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];
        
        // Add database notification for admin dashboard
        $channels[] = 'database';
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Course Purchase Confirmation - ' . $this->course->name)
            ->greeting('🎉 Purchase Successful!')
            ->line('Congratulations! Your course purchase has been successfully processed.')
            ->line('**Course:** ' . $this->course->name)
            ->line('**Price:** Rp ' . number_format($this->course->price, 0, ',', '.'))
            ->line('**Transaction ID:** ' . $this->transaction->booking_trx_id)
            ->line('You now have **lifetime access** to this course.')
            ->action('Start Learning Now', url('/course/' . $this->course->slug))
            ->line('Visit your dashboard to see all your purchased courses.')
            ->line('Thank you for choosing ' . \App\Helpers\WebsiteSettingHelper::get('site_name', 'LMS Platform') . '!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'course_purchased',
            'message' => "New course purchased: {$this->course->name}",
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'transaction_id' => $this->transaction->id,
            'transaction_code' => $this->transaction->booking_trx_id,
            'user_name' => $notifiable->name,
            'user_email' => $notifiable->email,
            'amount' => $this->transaction->grand_total_amount,
            'currency' => 'IDR',
            'purchased_at' => $this->transaction->created_at->toISOString(),
        ];
    }
}
