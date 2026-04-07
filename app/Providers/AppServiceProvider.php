<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fix for Livewire v4 missing ComponentRegistry (BindingResolutionException)
        if (class_exists(\Livewire\LivewireServiceProvider::class)) {
            $this->app->bind('Livewire\Mechanisms\ComponentRegistry', function () {
                return new class {
                    public function register($name, $class) {
                        if ($name === 'model' && class_exists(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)) {
                            app(\Livewire\Mechanisms\HandleComponents\HandleComponents::class)->registerPropertySynthesizer($class);
                        }
                    }
                    public function getClass($name) {
                        return app('livewire.finder')->resolveClassComponentClassName($name);
                    }
                    public function has($name) {
                        return !is_null(app('livewire.finder')->resolveClassComponentClassName($name));
                    }
                    public function get($name) {
                        return $this->getClass($name);
                    }
                    public function getName($class) {
                        // Minimal implementation for getName - might need mapping if used
                        return $class; 
                    }
                };
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
