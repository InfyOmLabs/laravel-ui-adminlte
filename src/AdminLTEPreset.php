<?php

namespace InfyOm\AdminLTEPreset;

use Illuminate\Console\Command;
use InfyOm\GeneratorHelpers\LaravelUtils;
use Laravel\Ui\Presets\Preset;

class AdminLTEPreset extends Preset
{
    /** @var Command */
    protected $command;

    protected $views = [
        'auth/login.stub' => 'auth/login.blade.php',
        'auth/passwords/confirm.stub' => 'auth/passwords/confirm.blade.php',
        'auth/passwords/email.stub' => 'auth/passwords/email.blade.php',
        'auth/passwords/reset.stub' => 'auth/passwords/reset.blade.php',
        'auth/register.stub' => 'auth/register.blade.php',
        'auth/verify.stub' => 'auth/verify.blade.php',
        'home.stub' => 'home.blade.php',
        'layouts/app.stub' => 'layouts/app.blade.php',
        'layouts/menu.stub' => 'layouts/menu.blade.php',
        'layouts/sidebar.stub' => 'layouts/sidebar.blade.php',
    ];

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Update the given package array.
     *
     * @param  array  $packages
     * @return array
     */
    protected static function updatePackageArray(array $packages)
    {
        return [
                'bootstrap' => '^4.0.0',
                'jquery' => '^3.2',
                'popper.js' => '^1.12',
                'admin-lte' => '^3.0',
            ] + $packages;
    }

    public function install()
    {
        static::updatePackages();
        static::updateSass();
        static::updateBootstrapping();
        static::removeNodeModules();
    }

    /**
     * Update the Sass files for the application.
     *
     * @return void
     */
    protected static function updateSass()
    {
        copy(__DIR__.'/../adminlte-stubs/bootstrap/_variables.scss', resource_path('sass/_variables.scss'));
        copy(__DIR__.'/../adminlte-stubs/bootstrap/app.scss', resource_path('sass/app.scss'));
    }

    /**
     * Update the bootstrapping files.
     *
     * @return void
     */
    protected static function updateBootstrapping()
    {
        copy(__DIR__.'/../adminlte-stubs/bootstrap/bootstrap.js', resource_path('js/bootstrap.js'));
        copy(__DIR__.'/../adminlte-stubs/bootstrap/app.js', resource_path('js/app.js'));
    }

    public function installAuth()
    {
        $viewsPath = LaravelUtils::getViewPath();

        $this->ensureDirectoriesExist($viewsPath);;

        foreach ($this->views as $key => $value) {
            if (file_exists($view = LaravelUtils::getViewPath($value))) {
                if (!$this->command->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__.'/../adminlte-stubs/'.$key,
                $view
            );

            $this->command->info("{$view} copied");
        }
    }

    protected function ensureDirectoriesExist($viewsPath)
    {
        if (!file_exists($viewsPath.'layouts')) {
            mkdir($viewsPath.'layouts', 0755, true);
        }

        if (!file_exists($viewsPath.'auth')) {
            mkdir($viewsPath.'auth', 0755, true);
        }

        if (!file_exists($viewsPath.'auth/passwords')) {
            mkdir($viewsPath.'auth/passwords', 0755, true);
        }
    }
}
