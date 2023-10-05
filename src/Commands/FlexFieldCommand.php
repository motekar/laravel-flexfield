<?php

namespace Motekar\FlexField\Commands;

use Illuminate\Console\Command;

class FlexFieldCommand extends Command
{
    public $signature = 'laravel-flexfield';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
