<?php

namespace Gruz\FPBX\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallFPBXPackage extends Command
{
    protected $signature = 'fpbx:install';

    protected $description = 'Install the FPBX';

    public function handle()
    {
        $this->info('Installing FPBX...');

        $this->info('Publishing configuration...');

        if (! $this->configExists('fpbx.php')) {
            $this->publishConfiguration();
            $this->info('Published configuration');
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting configuration file...');
                $this->publishConfiguration($force = true);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }

        $this->info('Installed FPBX');
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function shouldOverwriteConfig()
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "Gruz\\FPBX\\Providers\\FPBXAppServiceProvider",
            '--tag' => "config"
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

       $this->call('vendor:publish', $params);
    }
}
