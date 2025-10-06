<?php

namespace App\Notifications;

use App\Models\Programa;
use App\Models\Fase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FaseLiberada extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Programa $programa,
        public Fase $faseCompletada,
        public Fase $siguienteFase
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
        return (new MailMessage)
            ->subject('Nueva Fase Liberada - ' . $this->siguienteFase->nombre)
            ->line("La fase '{$this->faseCompletada->nombre}' ha sido completada.")
            ->line("Programa: {$this->programa->nombre}")
            ->line("Ahora puedes trabajar en: '{$this->siguienteFase->nombre}'")
            ->action('Ver Programa', url('/admin/programas/' . $this->programa->id . '/edit'))
            ->line('¡Es tu turno de trabajar en esta fase!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'programa_id' => $this->programa->id,
            'programa_nombre' => $this->programa->nombre,
            'fase_completada' => $this->faseCompletada->nombre,
            'siguiente_fase_id' => $this->siguienteFase->id,
            'siguiente_fase' => $this->siguienteFase->nombre,
        ];
    }
}
