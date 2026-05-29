<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakePluginModelCommand extends Command
{
    protected $signature = 'make:plugin-model {plugin : The plugin name} {name : The model name}';
    protected $description = 'Create a new model for a plugin';

    public function handle(): int
    {
        $plugin = strtolower(Str::snake($this->argument('plugin')));
        $name = Str::studly($this->argument('name'));
        $table = Str::snake(Str::pluralStudly($name));
        $prefix = "{$plugin}_";

        $basePath = base_path("plugins/mitunierp/{$plugin}/src/Models");
        $path = "{$basePath}/{$name}.php";

        if (!is_dir($basePath)) {
            $this->error("Plugin '{$plugin}' not found");

            return self::FAILURE;
        }

        if (File::exists($path)) {
            $this->error("Model {$name} already exists");

            return self::FAILURE;
        }

        $pluginStudly = Str::studly($plugin);

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace Mitunierp\\{$pluginStudly}\Models;

        use Illuminate\Database\Eloquent\Attributes\Fillable;
        use Illuminate\Database\Eloquent\Model;

        #[Fillable([])]
        final class {$name} extends Model
        {
            protected \$table = '{$prefix}{$table}';

            /**
             * @return array<string, string>
             */
            protected function casts(): array
            {
                return [];
            }
        }
        PHP;

        File::put($path, $content);

        $this->info("Model {$name} created for plugin '{$plugin}'");

        return self::SUCCESS;
    }
}
