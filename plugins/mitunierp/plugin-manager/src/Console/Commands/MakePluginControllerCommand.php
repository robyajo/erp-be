<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakePluginControllerCommand extends Command
{
    protected $signature = 'make:plugin-controller {plugin : The plugin name} {name : The controller name (e.g., Product)}';
    protected $description = 'Create a new API controller for a plugin';

    public function handle(): int
    {
        $plugin = strtolower(Str::snake($this->argument('plugin')));
        $name = Str::studly($this->argument('name'));
        $pluginStudly = Str::studly($plugin);

        $basePath = base_path("plugins/mitunierp/{$plugin}/src/Http/Controllers/API/V1");
        $path = "{$basePath}/{$name}Controller.php";

        if (!is_dir($basePath)) {
            $this->error("Plugin '{$plugin}' not found at plugins/mitunierp/{$plugin}");

            return self::FAILURE;
        }

        if (File::exists($path)) {
            $this->error("Controller {$name}Controller already exists");

            return self::FAILURE;
        }

        $modelNamespace = "Mitunierp\\{$pluginStudly}\\Models\\{$name}";
        $modelVariable = lcfirst($name);

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace Mitunierp\\{$pluginStudly}\Http\Controllers\API\V1;

        use App\Http\Controllers\Api\ApiController;
        use Illuminate\Http\JsonResponse;
        use Illuminate\Http\Request;
        use Mitunierp\\{$pluginStudly}\Models\\{$name};
        use Spatie\QueryBuilder\AllowedFilter;
        use Spatie\QueryBuilder\QueryBuilder;

        final class {$name}Controller extends ApiController
        {
            public function index(Request \$request): JsonResponse
            {
                \${$modelVariable}s = QueryBuilder::for({$name}::class)
                    ->allowedFilters([])
                    ->allowedSorts(['name', 'created_at'])
                    ->defaultSort('name')
                    ->paginate(\$request->input('per_page', 15));

                return \$this->success(\${$modelVariable}s);
            }

            public function show({$name} \${$modelVariable}): JsonResponse
            {
                return \$this->success(\${$modelVariable});
            }

            public function store(Request \$request): JsonResponse
            {
                \$validated = \$request->validate([]);

                \${$modelVariable} = {$name}::query()->create(\$validated);

                return \$this->created(\${$modelVariable}, '{$name} created.');
            }

            public function update(Request \$request, {$name} \${$modelVariable}): JsonResponse
            {
                \$validated = \$request->validate([]);

                \${$modelVariable}->update(\$validated);

                return \$this->success(\${$modelVariable}, '{$name} updated.');
            }

            public function destroy({$name} \${$modelVariable}): JsonResponse
            {
                \${$modelVariable}->delete();

                return \$this->noContent();
            }
        }
        PHP;

        File::put($path, $content);

        $this->info("Controller {$name}Controller created for plugin '{$plugin}'");
        $this->warn("Don't forget to add the route in routes/api.php:");
        $this->line("    Route::apiResource('{$modelVariable}s', {$name}Controller::class);");

        return self::SUCCESS;
    }
}
