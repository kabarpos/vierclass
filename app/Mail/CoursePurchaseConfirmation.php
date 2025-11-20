<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoursePurchaseConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Course $course, Transaction $transaction)
    {
        $this->user = $user;
        $this->course = $course;
        $this->transaction = $transaction;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Course Purchase Confirmation - ' . $this->course->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.course-purchase-confirmation',
            text: 'emails.course-purchase-confirmation',
            with: [
                'user' => $this->user,
                'course' => $this->course,
                'transaction' => $this->transaction,
                'courseThumbnail' => $this->course->thumbnail ? asset('storage/' . $this->course->thumbnail) : null,
                'courseUrl' => url('/course/' . $this->course->slug),
                'dashboardUrl' => url('/dashboard'),
                'supportUrl' => url('/contact'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}