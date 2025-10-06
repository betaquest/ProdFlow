<?php

namespace App\Notifications;

use App\Models\AvanceFase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class AvanceFaseActualizado extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AvanceFase $avanceFase,
        public string $estadoAnterior
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $programa = $this->avanceFase->programa;
        $fase = $this->avanceFase->fase;

        return (new MailMessage)
            ->subject('Actualización de Avance de Fase')
            ->line("El estado del avance ha cambiado de '{$this->estadoAnterior}' a '{$this->avanceFase->estado}'.")
            ->line("Programa: {$programa->nombre}")
            ->line("Fase: {$fase->nombre}")
            ->action('Ver Avance', url('/admin/avance-fases/' . $this->avanceFase->id . '/edit'))
            ->line('Gracias por usar nuestro sistema!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'avance_fase_id' => $this->avanceFase->id,
            'programa' => $this->avanceFase->programa->nombre,
            'fase' => $this->avanceFase->fase->nombre,
            'estado_anterior' => $this->estadoAnterior,
            'estado_nuevo' => $this->avanceFase->estado,
        ];
    }
}
