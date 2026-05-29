<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakePluginMigrationCommand extends Command
{
    protected $signature = 'make:plugin-migration {plugin : The plugin name} {name : The migration name}';
    protected $description = 'Create a new migration for a plugin';

    public function handle(): int
    {
        $plugin = strtolower(Str::snake($this->argument('plugin')));
        $name = $this->argument('name');
        $table = Str::snake(Str::pluralStudly($name));
        $prefix = "{$plugin}_";

        $migrationDir = base_path("plugins/mitunierp/{$plugin}/database/migrations");

        if (!is_dir($migrationDir)) {
            $this->error("Plugin '{$plugin}' not found");

            return self::FAILURE;
        }

        $timestamp = now()->format('Y_m_d_His');
        $filename = "{$timestamp}_create_{$prefix}{$table}_table.php";
        $path = "{$migrationDir}/{$filename}";

        $pluginStudly = Str::studly($plugin);

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('{$prefix}{$table}', function (Blueprint \$table): void {
                    \$table->id();
                    \$table->timestamps();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('{$prefix}{$table}');
            }
        };
        PHP;

        File::put($path, $content);

        $this->info("Migration created: {$filename}");
        $this->warn("Remember to add '{$filename}' to the hasMigrations() array in the ServiceProvider");

        return self::SUCCESS;
    }
}
