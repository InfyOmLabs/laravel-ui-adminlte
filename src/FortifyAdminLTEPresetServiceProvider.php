<?php

namespace InfyOm\AdminLTEPreset;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Ui\UiCommand;

class FortifyAdminLTEPresetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        UiCommand::macro('adminlte-fortify', function (UiCommand $command) {
            $fortifyAdminLTEPreset = new AdminLTEPreset($command, true);
            $fortifyAdminLTEPreset->install();

            $command->info('AdminLTE scaffolding installed successfully for Laravel Fortify.');

            if ($command->option('auth')) {
                $fortifyAdminLTEPreset->installAuth();
                $command->info('AdminLTE CSS auth scaffolding installed successfully for Laravel Fortify.');
            }

            $command->comment('Please run "npm install && npm run dev" to compile your fresh scaffolding.');
        });

        Fortify::loginView(function () {
            view('auth.login');
        });

        Fortify::registerView(function () {
            view('auth.register');
        });

        Fortify::confirmPasswordView(function () {
            view('auth.passwords.confirm');
        });

        Fortify::requestPasswordResetLinkView(function () {
            view('auth.passwords.email');
        });

        Fortify::resetPasswordView(function (Request $request) {
            view('auth.passwords.reset');
        });

        Fortify::verifyEmailView(function () {
            view('auth.verify');
        });
    }
}
