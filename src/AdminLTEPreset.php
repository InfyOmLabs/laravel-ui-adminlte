<?php

namespace InfyOm\AdminLTEPreset;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InfyOm\GeneratorHelpers\LaravelUtils;
use Laravel\Ui\Presets\Preset;
use Symfony\Component\Finder\SplFileInfo;

class AdminLTEPreset extends Preset
{
    /** @var Command */
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Update the given package array.
     *
     * @param array $packages
     *
     * @return array
     */
    protected static function updatePackageArray(array $packages)
    {
        return [
            'bootstrap' => '^4.0.0',
            'jquery'    => '^3.2',
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

        $this->ensureDirectoriesExist($viewsPath);

        $this->scaffoldAuth();
        $this->scaffoldController();
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

    protected function scaffoldController()
    {
        if (!is_dir($directory = app_path('Http/Controllers/Auth'))) {
            mkdir($directory, 0755, true);
        }

        $filesystem = new Filesystem();

        collect($filesystem->allFiles(base_path('vendor/laravel/ui/stubs/Auth')))
            ->each(function (SplFileInfo $file) use ($filesystem) {
                $filesystem->copy(
                    $file->getPathname(),
                    app_path('Http/Controllers/Auth/'.Str::replaceLast('.stub', '.php', $file->getFilename()))
                );
            });
    }

    protected function scaffoldAuth()
    {
        file_put_contents(app_path('Http/Controllers/HomeController.php'), $this->compileHomeControllerStub());

        file_put_contents(
            base_path('routes/web.php'),
            "Auth::routes();\n\nRoute::get('/home', 'HomeController@index')->name('home');\n\n",
            FILE_APPEND
        );

        tap(new Filesystem(), function ($filesystem) {
            $filesystem->copyDirectory(__DIR__.'/../adminlte-stubs/auth', resource_path('views/auth'));
            $filesystem->copyDirectory(__DIR__.'/../adminlte-stubs/layouts', resource_path('views/layouts'));
            $filesystem->copy(__DIR__.'/../adminlte-stubs/home.blade.php', resource_path('views/home.blade.php'));

            collect($filesystem->allFiles(base_path('vendor/laravel/ui/stubs/migrations')))
                ->each(function (SplFileInfo $file) use ($filesystem) {
                    $filesystem->copy(
                        $file->getPathname(),
                        database_path('migrations/'.$file->getFilename())
                    );
                });
        });
    }

    protected function compileHomeControllerStub()
    {
        return str_replace(
            '{{namespace}}',
            Container::getInstance()->getNamespace(),
            file_get_contents(base_path('vendor/laravel/ui/src/Auth/stubs/controllers/HomeController.stub'))
        );
    }
}
