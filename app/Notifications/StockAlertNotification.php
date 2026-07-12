<?php

namespace App\Notifications;

use App\Models\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification
{
    use Queueable;

    protected Stock $stock;
    protected int $alertId;

    /**
     * Create a new notification instance.
     */
    public function __construct(Stock $stock, int $alertId)
    {
        $this->stock   = $stock;
        $this->alertId = $alertId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $statut = $this->stock->quantite <= 0 ? 'RUPTURE DE STOCK' : 'Seuil d\'alerte atteint';

        return (new MailMessage)
            ->subject('⚠️ Alerte stock — ' . $this->stock->product->nom)
            ->greeting('Bonjour ' . $notifiable->full_name . ',')
            ->line("Le produit **{$this->stock->product->nom}** ({$this->stock->emplacement->nom}) nécessite votre attention.")
            ->line("Statut : **{$statut}**")
            ->line("Stock actuel : {$this->stock->quantite} / Seuil : {$this->stock->seuil_alerte}")
            ->action('Voir le produit', config('app.frontend_url', config('app.url')) . '/product')
            ->line('Merci de procéder à un réapprovisionnement si nécessaire.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'alert_id'     => $this->alertId,
            'title'        => 'Alerte de stock',
            'message'      => "Le produit \"{$this->stock->product->nom}\" a atteint le seuil d'alerte.",
            'product_id'   => $this->stock->product_id,
            'nom'          => $this->stock->product->nom,
            'code_barre'   => $this->stock->product->code_barre,
            'emplacement'  => $this->stock->emplacement->nom,
            'quantite'     => $this->stock->quantite,
            'seuil_alerte' => $this->stock->seuil_alerte,
            'rupture'      => $this->stock->quantite <= 0,
        ];
    }
}
