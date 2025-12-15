<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlmacenSyncService;

class TestAlmacenesConnection extends Command
{
    protected $signature = 'test:almacenes';
    protected $description = 'Test connection to plantaCruds almacenes API';

    public function handle()
    {
        $this->info('Testing connection to plantaCruds almacenes API...');
        
        $service = new AlmacenSyncService();
        
        // Limpiar cache primero
        $service->clearCache();
        $this->info('Cache cleared.');
        
        // Obtener almacenes
        $almacenes = $service->getAlmacenes(true);
        
        if (empty($almacenes)) {
            $this->error('❌ No se pudieron obtener almacenes desde plantaCruds');
            $this->info('Verifique:');
            $this->info('1. Que plantaCruds esté corriendo');
            $this->info('2. Que PLANTACRUDS_API_URL esté configurado correctamente en .env');
            $this->info('3. Que el endpoint /api/almacenes esté disponible');
            return 1;
        }
        
        $this->info("✅ Se obtuvieron " . count($almacenes) . " almacenes:");
        foreach ($almacenes as $alm) {
            $tipo = ($alm['es_planta'] ?? false) ? 'PLANTA' : 'DESTINO';
            $this->line("  - [{$tipo}] {$alm['nombre']} (ID: {$alm['id']})");
        }
        
        $destinos = $service->getDestinoAlmacenes();
        $this->info("\n📦 Almacenes de destino: " . count($destinos));
        
        $planta = $service->getPlantaAlmacen();
        if ($planta) {
            $this->info("🏭 Almacén planta: {$planta['nombre']} (ID: {$planta['id']})");
        } else {
            $this->warn("⚠️ No se encontró almacén planta");
        }
        
        return 0;
    }
}

