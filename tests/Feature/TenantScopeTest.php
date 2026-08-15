<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\Finder;

class TenantScopeTest extends TestCase
{
    /**
     * Teste que tous les modèles qui définissent une relation client_id
     * appliquent bien le TenantScope (ou un équivalent) pour éviter l'intrusion.
     */
    public function test_all_tenant_models_have_scope()
    {
        $modelsPath = app_path('Models');
        $finder = new Finder();
        $finder->files()->name('*.php')->in($modelsPath);

        $unscopedModels = [];

        foreach ($finder as $file) {
            $class = 'App\\Models\\' . str_replace('/', '\\', $file->getRelativePathname());
            $class = preg_replace('/\.php$/', '', $class);

            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $reflection = new \ReflectionClass($class);
                
                if (!$reflection->isInstantiable()) {
                    continue; // Skip abstracts
                }

                $model = new $class;
                
                // Si le modèle a une colonne client_id (ou si on le suppose tenant-aware)
                if (\Schema::hasColumn($model->getTable(), 'client_id') || \Schema::hasColumn($model->getTable(), 'cabinet_id')) {
                    // Vérifier si le scope TenantScope ou equivalent est appliqué
                    $scopes = $model->getGlobalScopes();
                    
                    $hasTenantScope = false;
                    foreach ($scopes as $scopeName => $scopeClosure) {
                        if (is_string($scopeName) && (str_contains($scopeName, 'TenantScope') || str_contains($scopeName, 'client'))) {
                            $hasTenantScope = true;
                            break;
                        }
                    }

                    if (!$hasTenantScope) {
                        $unscopedModels[] = $class;
                    }
                }
            }
        }

        $this->assertEmpty($unscopedModels, 'Les modèles suivants n\'ont pas de TenantScope appliqué : ' . implode(', ', $unscopedModels));
    }
}
