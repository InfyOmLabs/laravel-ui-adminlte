<?php

namespace InfyOm\AdminLTEPreset;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Laravel\Ui\UiCommand;

class AdminLTEPresetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        UiCommand::macro('adminlte', function (Command $command) {

            $command->info("AdminLTE Preset called");

            $adminLTEPreset = new AdminLTEPreset($command);
            $adminLTEPreset->install();

            $command->info('AdminLTE scaffolding installed successfully.');

            if ($command->option('auth')) {
                $adminLTEPreset->installAuth();
                $command->info('AdminLTE CSS auth scaffolding installed successfully.');
            }

            $command->comment('Please run "npm install && npm run dev" to compile your fresh scaffolding.');
        });

//        Paginator::defaultView('pagination::default');

//        Paginator::defaultSimpleView('pagination::simple-default');
    }
}
