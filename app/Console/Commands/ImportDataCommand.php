<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DummyJsonImporterService; 
use Illuminate\Support\Facades\Log;


class ImportProductsCommand extends Command 
{
    /**
     * Имя и сигнатура консольной команды.
     * 
     * 
     *
     * @var string
     */
 
 
    protected $signature = 'import:data {type : Тип данных для импорта (products, recipes, posts, users)}
                                       {--search= : Опциональный поисковый запрос}';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Импортирует данные (products, recipes, posts, users) из dummyjson.com API';

    /**
     * Экземпляр сервиса импорта.
     * @var DummyJsonImporterService
     */
    protected DummyJsonImporterService $importerService;

    /**
     * Создание нового экземпляра команды.
     * 
     *
     * @param DummyJsonImporterService $importerService
     * @return void
     */
    public function __construct(DummyJsonImporterService $importerService)
    {
        parent::__construct();
        $this->importerService = $importerService; 
    }

    /**
     * Выполнение консольной команды.
     *
     * @return int
     */
    public function handle(): int
    {
        $dataType = $this->argument('type'); 
        $searchTerm = $this->option('search'); 

        
        $allowedTypes = ['products', 'recipes', 'posts', 'users']; 
        if (!in_array($dataType, $allowedTypes)) {
            $this->error("Недопустимый тип данных '{$dataType}'. Разрешенные типы: " . implode(', ', $allowedTypes));
            return Command::INVALID; // Код ошибки для неверных аргументов
        }

        $this->info("Запуск импорта для типа данных: {$dataType}" . ($searchTerm ? " по запросу: '{$searchTerm}'" : " (все записи)"));

        try {
 
            $result = $this->importerService->importData($dataType, $searchTerm);

 
            $this->info("Импорт '{$dataType}' завершен. Импортировано/Обновлено: {$result['imported']}. Пропущено: {$result['skipped']}.");
            return Command::SUCCESS; // Код успешного выполнения

        } catch (RequestException $e) {
            $this->error("Ошибка API: Не удалось получить данные для '{$dataType}'. Статус: {$e->response->status()}.");
 
            return Command::FAILURE; // Код общей ошибки выполнения
        } catch (Exception $e) {
 
            $this->error("Произошла ошибка во время импорта '{$dataType}': " . $e->getMessage());
            Log::error("Ошибка в команде import:data ({$dataType}): " . $e->getMessage()); // Логируем на уровне команды
            return Command::FAILURE;
        }
    }
}
