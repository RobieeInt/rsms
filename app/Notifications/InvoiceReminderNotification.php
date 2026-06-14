<?php

namespace App\Notifications;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification
{
    public function __construct(
        public Invoice $invoice,
        public string $reminderType
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Carbon::setLocale('id');

        $number = $this->invoice->invoice_number;
        $amount = 'Rp ' . number_format($this->invoice->total_amount, 0, ',', '.');
        $due    = $this->invoice->due_date->locale('id')->translatedFormat('d F Y');

        [$subject, $intro, $urgency] = match ($this->reminderType) {
            '7_days_before' => [
                "Pengingat Pembayaran Invoice {$number} — 7 Hari Lagi",
                'Invoice Anda akan jatuh tempo dalam **7 hari**.',
                'Mohon segera lakukan persiapan pembayaran.',
            ],
            '3_days_before' => [
                "Pengingat Pembayaran Invoice {$number} — 3 Hari Lagi",
                'Invoice Anda akan jatuh tempo dalam **3 hari**.',
                'Segera lakukan pembayaran sebelum tanggal jatuh tempo.',
            ],
            'due_today' => [
                "Invoice {$number} Jatuh Tempo Hari Ini",
                'Invoice Anda **jatuh tempo hari ini**.',
                'Harap lakukan pembayaran secepatnya untuk menghindari keterlambatan.',
            ],
            '7_days_overdue' => [
                "⚠️ Invoice {$number} Telah Melewati Jatuh Tempo 7 Hari",
                'Invoice Anda telah **melewati tanggal jatuh tempo selama 7 hari**.',
                'Mohon segera hubungi kami dan lakukan pembayaran untuk menghindari dampak lebih lanjut.',
            ],
            default => [
                "Pengingat Invoice {$number}",
                'Harap periksa invoice Anda.',
                '',
            ],
        };

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Yth. ' . ($notifiable->pic_name ?? 'Bapak/Ibu') . ',')
            ->line($intro)
            ->line('**No. Invoice:** ' . $number)
            ->line('**Jumlah:** ' . $amount)
            ->line('**Tanggal Jatuh Tempo:** ' . $due);

        if ($urgency) {
            $mail->line($urgency);
        }

        return $mail
            ->action('Lihat Invoice', route('invoices.show', $this->invoice))
            ->salutation('Terima kasih,<br>Reconext Digital Kreasi');
    }
}
