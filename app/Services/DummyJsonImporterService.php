<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Database\QueryException;

class DummyJsonImporterService
{
 
    protected array $config = [
        'products' => [
            'model' => Product::class,
            'api_endpoint' => 'https://dummyjson.com/products',
            'search_endpoint' => 'https://dummyjson.com/products/search?q=',
            'data_key' => 'products',
            'external_id_field' => 'id',
            'field_map' => [
                'id' => 'external_id',
                'title' => 'title',
                'description' => 'description',
                'price' => 'price',
                'discountPercentage' => 'discountPercentage',
                'rating' => 'rating',
                'stock' => 'stock',
                'brand' => 'brand',
                'category' => 'category',
                'thumbnail' => 'thumbnail',
            ],
        ],
        'recipes' => [
            'model' => Recipe::class,
            'api_endpoint' => 'https://dummyjson.com/recipes',
            'search_endpoint' => 'https://dummyjson.com/recipes/search?q=',
            'data_key' => 'recipes',
            'external_id_field' => 'id',
            'field_map' => [
                'id' => 'external_id',
                'name' => 'name',
                'ingredients' => 'ingredients',
                'instructions' => 'instructions',
                'prepTimeMinutes' => 'prep_time_minutes',
                'cookTimeMinutes' => 'cook_time_minutes',
                'servings' => 'servings',
                'difficulty' => 'difficulty',
                'cuisine' => 'cuisine',
                'caloriesPerServing' => 'calories_per_serving',
                'rating' => 'rating',
                'image' => 'image_url',
            ],
        ],
        'posts' => [
            'model' => Post::class,
            'api_endpoint' => 'https://dummyjson.com/posts',
            'data_key' => 'posts',
            'external_id_field' => 'id',
            'field_map' => [
                 'id' => 'external_id',
                 'title' => 'title',
                 'body' => 'body',
                 'userId' => 'user_external_id',
                 'tags' => 'tags',
                 'reactions' => 'reactions',
            ],
        ],
         'users' => [
            'model' => User::class,
            'api_endpoint' => 'https://dummyjson.com/users',
            'search_endpoint' => 'https://dummyjson.com/users/search?q=',
            'data_key' => 'users',
            'external_id_field' => 'id',
            'field_map' => [
                'id' => 'external_id',
                'firstName' => 'first_name',
                'lastName' => 'last_name',
                'email' => 'email',
                'phone' => 'phone',
                'username' => 'username',
                'image' => 'image_url',
 
                'password' => 'password', // Хотя мы его не используем, но он есть в API
                'name' => 'name', // Мы его не используем, но он есть в модели
            ],
        ],
    ];

 
    protected array $currentConfig;

    /**
     * Импортирует данные указанного типа.
     *
     * @param string $dataType Тип данных ('products', 'recipes', 'posts', 'users')
     * @param string|null $searchTerm Опциональный поисковый запрос
     * @return array ['imported' => int, 'skipped' => int] Результат импорта
     * @throws Exception Если тип данных не поддерживается или ошибка API/DB
     */
    public function importData(string $dataType, ?string $searchTerm = null): array
    {
        if (!isset($this->config[$dataType])) {
            throw new Exception("Неподдерживаемый тип данных: {$dataType}");
        }

        $this->currentConfig = $this->config[$dataType];
        $modelClass = $this->currentConfig['model'];

        if (!class_exists($modelClass)) {
             throw new Exception("Класс модели не найден: {$modelClass}");
        }

 
        $url = $this->buildApiUrl($dataType, $searchTerm);

 
        $items = $this->fetchFromApi($url, $dataType);

 
        $result = $this->saveData($items, $dataType, $modelClass);

        return $result;
    }

    /**
     * Строит URL для запроса к API, добавляя limit=0 для получения всех записей.
     */
    protected function buildApiUrl(string $dataType, ?string $searchTerm): string
    {
        if ($searchTerm && !empty($this->currentConfig['search_endpoint'])) {
            $url = $this->currentConfig['search_endpoint'] . urlencode($searchTerm);
 
            return $url . (strpos($url, '?') === false ? '?' : '&') . 'limit=0';
        }
 
        return $this->currentConfig['api_endpoint'] . '?limit=0';
    }

    /**
     * Получает данные из API.
     * @return array Массив элементов данных
     * @throws RequestException|Exception
     */
    protected function fetchFromApi(string $url, string $dataType): array
    {
        Log::info("Получение данных ({$dataType}) с: {$url}");
        $response = Http::get($url);

        if (!$response->successful()) {
            Log::error("Ошибка запроса к API ({$dataType}). Статус: {$response->status()}. URL: {$url}. Ответ: " . $response->body());
            throw new RequestException($response);
        }

        $data = $response->json();
        $dataKey = $this->currentConfig['data_key'];

 
        if (!isset($data[$dataKey]) || !is_array($data[$dataKey])) {
             Log::error("Ответ API ({$dataType}) не содержит ожидаемый ключ '{$dataKey}' или он не является массивом. URL: {$url}. Ответ: " . $response->body());
             throw new Exception("Неверная структура ответа API для {$dataType}. Ожидался массив '{$dataKey}'.");
        }

 
        return $data[$dataKey];
    }

    /**
     * Сохраняет пачку полученных данных в базу данных, используя updateOrCreate.
     * Версия 2: Упрощенная для пагинации.
     */
    protected function saveData(array $items, string $dataType, string $modelClass): array
    {
        $importedCount = 0;
        $skippedCount = 0;
        $externalIdFieldApi = $this->currentConfig['external_id_field'];
        $fieldMap = $this->currentConfig['field_map'];
        $externalIdFieldDb = $fieldMap[$externalIdFieldApi] ?? 'external_id';

        if (empty($items)) {
            return ['imported' => 0, 'skipped' => 0];
        }

 
        $modelInstanceForCasts = new $modelClass;
        $casts = $modelInstanceForCasts->getCasts();
        $jsonCastableFields = [];
        foreach ($casts as $field => $type) {
            if (in_array($type, ['array', 'json', 'object', 'collection'])) {
                $jsonCastableFields[] = $field;
            }
        }
        unset($modelInstanceForCasts);

        foreach ($items as $itemData) {
            if (!isset($itemData[$externalIdFieldApi])) {
                Log::warning("Пропуск элемента ({$dataType}) из-за отсутствия поля внешнего ID ('{$externalIdFieldApi}'). Данные: " . json_encode($itemData));
                $skippedCount++;
                continue;
            }

            $externalId = $itemData[$externalIdFieldApi];
            $attributesToPass = []; // Атрибуты для передачи в updateOrCreate

             foreach ($fieldMap as $apiField => $modelField) {
                if (isset($itemData[$apiField])) {
                    $value = $itemData[$apiField];
 
                    if (is_array($value) && !in_array($modelField, $jsonCastableFields)) {
                         Log::warning("API поле '{$apiField}' (-> '{$modelField}') вернуло массив, но поле не кастуется к JSON/массиву. Тип данных: {$dataType}, ID: {$externalId}. Поле пропущено.");
                    } else {
 
                        $attributesToPass[$modelField] = $value;
                    }
                }
            }

             $searchCondition = [$externalIdFieldDb => $externalId];
 
            if (empty($attributesToPass)) {
                 Log::warning("Пропуск элемента {$dataType} с ID {$externalId}, т.к. не удалось собрать атрибуты из fieldMap/itemData.");
                 $skippedCount++;
                 continue;
            }

            try {
                   $modelInstance = $modelClass::updateOrCreate($searchCondition, $attributesToPass);
 
                if ($modelInstance->wasRecentlyCreated) {
                    $importedCount++;

                 } elseif ($modelInstance->wasChanged()) {
                    $importedCount++;
 
                } else {
                    $skippedCount++; // Запись найдена, но не изменилась
 
                }

            } catch (QueryException $e) {
                Log::error("Ошибка БД при импорте {$dataType} ID {$externalId}: " . $e->getMessage() . ". Данные: " . json_encode($attributesToPass));
                $skippedCount++;
            } catch (Exception $e) {
                Log::error("Общая ошибка при обработке {$dataType} ID {$externalId}: " . $e->getMessage());
                Log::debug("Данные для updateOrCreate {$dataType} ID {$externalId}: " . json_encode($attributesToPass));
                $skippedCount++;
            }
        }

        return ['imported' => $importedCount, 'skipped' => $skippedCount];
    }
}
