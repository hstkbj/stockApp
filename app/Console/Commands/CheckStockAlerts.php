<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\StockAlert;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Notifications\DatabaseNotification;

#[Signature('stock:check-alerts')]
#[Description('Vérifie les stocks en alerte et notifie Admin/Superadmin/Gerant (une seule alerte active tant que non lue ou non résolue)')]
class CheckStockAlerts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification des stocks...');

        $stocksAlerte = Stock::with('product', 'emplacement')
            ->whereColumn('quantite', '<=', 'seuil_alerte')
            ->get();

        $users = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Admin', 'Superadmin', 'Gerant']);
        })->get();

        if ($users->isEmpty()) {
            $this->error('Aucun utilisateur Admin/Superadmin/Gerant trouvé.');
            return self::FAILURE;
        }

        $cles_en_alerte = [];

        foreach ($stocksAlerte as $stock) {
            $cle = $stock->product_id . '-' . $stock->emplacement_id;
            $cles_en_alerte[] = $cle;

            $alert = StockAlert::where('product_id', $stock->product_id)
                ->where('emplacement_id', $stock->emplacement_id)
                ->where('status', 'active')
                ->first();

            if (! $alert) {
                // Nouvelle alerte : on notifie immédiatement
                $alert = StockAlert::create([
                    'product_id'     => $stock->product_id,
                    'emplacement_id' => $stock->emplacement_id,
                    'status'         => 'active',
                ]);

                $this->notifierUtilisateurs($alert, $stock, $users);
                continue;
            }

            // Alerte déjà active : on vérifie si TOUT le monde a lu la dernière notification
            $ids = $alert->last_notification_ids ?? [];
            $nonLues = DatabaseNotification::whereIn('id', $ids)->whereNull('read_at')->count();

            if (empty($ids) || $nonLues === 0) {
                // Tout a été lu (ou rien n'a jamais été envoyé) et le problème persiste -> on relance
                $this->notifierUtilisateurs($alert, $stock, $users);
            } else {
                $this->line("↷ {$stock->product->nom} : alerte déjà en attente de lecture, pas de renvoi.");
            }
        }

        // Résoudre les alertes des produits remontés au-dessus du seuil
        $resolues = StockAlert::where('status', 'active')->get()->filter(function ($alert) use ($cles_en_alerte) {
            return ! in_array($alert->product_id . '-' . $alert->emplacement_id, $cles_en_alerte);
        });

        foreach ($resolues as $alert) {
            $alert->update(['status' => 'resolved', 'resolved_at' => now()]);
            $this->info("✔ Alerte résolue pour le produit #{$alert->product_id} (réapprovisionné).");
        }

        $this->info('Vérification terminée.');
        return self::SUCCESS;
    }

     protected function notifierUtilisateurs(StockAlert $alert, Stock $stock, $users)
    {
        $ids = [];

        foreach ($users as $user) {
            $user->notify(new StockAlertNotification($stock, $alert->id));
            $derniere = $user->notifications()->latest()->first();
            if ($derniere) {
                $ids[] = $derniere->id;
            }
        }

        $alert->update([
            'last_notification_ids' => $ids,
            'last_notified_at'      => now(),
        ]);

        $this->warn("→ Notification envoyée pour {$stock->product->nom} à " . $users->pluck('full_name')->implode(', '));
    }
}
