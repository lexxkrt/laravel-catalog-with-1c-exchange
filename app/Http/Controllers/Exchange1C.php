<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Product;
use App\Models\Property;
use App\Models\PropertyValue;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Exchange1C extends Controller
{
    private $categories = [];
    private $brands = [];
    private $properties = [];
    private $filter_groups = [];
    private $values = [];

    public function __invoke()
    {
        $mode = request()->input("mode");
        $type = request()->input("type");

        if (isset($mode) && $type == "catalog") {
            switch ($mode) {
                case 'checkauth':
                    $this->checkauth();
                    break;
                case 'init':
                    $this->catalogInit();
                    break;
                case 'file':
                    $this->catalogFile();
                    break;
                case 'import':
                    $this->catalogImport();
                    break;
                case 'manual':
                    $this->manual();
                    break;
                default:
                    echo "failure\n";
                    echo "error command\n";
                    break;
            }
        } else if (isset($mode) && $type == "sale") {
            switch ($mode) {
                case 'checkauth':
                    $this->checkauth();
                    break;
                case 'init':
                    $this->saleInit();
                    echo "success\n";
                    break;
                case 'file':
                    $this->saleFile();
                    break;
                case "query":
                    $this->saleQuery();
                    echo "success\n";
                    break;
                default:
                    echo "failure\n";
                    echo "error command\n";
                    break;
            }
        } else {
            return redirect("404");
        }
    }

    private function saleInit()
    {
        $limit = 100000 * 1024;
        echo "zip=no\n";
        echo "file_limit=" . $limit . "\n";
    }

    private function saleFile()
    {
        $this->checkAccess();

        $filename = request()->input('filename');
        if (!isset($filename) || strpos($filename, 'orders') === false) {
            echo "failure\n";
            echo "No filename variable\n";
            exit;
        }

        $upload_file = Storage::disk('local')->path("cache/exchange/{$filename}");

        File::exists(dirname($upload_file)) or File::makeDirectory(dirname($upload_file), 0755, true);

        $data = file_get_contents("php://input");

        // $data = request()->getContent();

        if ($data !== false) {
            if (File::put($upload_file, $data)) {
                echo "success\n";
                chmod($upload_file, 0755);
                exit;
            } else {
                echo "failure\n";
                echo "Can't open file: $upload_file\n";
                exit;
            }
        }
        echo "failure\n";
        echo "No data file\n";
    }

    private function saleQuery()
    {
        // get orders to xml format
    }

    private function checkauth()
    {
        if (request()->server("PHP_AUTH_USER") != env("1C_USER")) {
            echo "failure\n";
            echo "user '" . env("1C_USER") . "'\n";
            echo "error login\n";
            exit;
        }

        if (request()->server("PHP_AUTH_PW") != env("1C_PASSWORD")) {
            echo "failure\n";
            echo "error login\n";
            exit;
        }

        Cookie::queue('key', md5(env('1C_PASSWORD')), 3600);
        echo "success\n";
        echo "key\n";
        echo md5(env("1C_PASSWORD")) . "\n";
    }

    private function catalogInit()
    {
        $limit = 100000 * 1024;
        echo "zip=no\n";
        echo "file_limit=" . $limit . "\n";
    }

    private function checkAccess()
    {
        if (!request()->hasCookie("key")) {
            echo "failure\n";
            echo "no cookie\n";
            exit;
        }
        if (request()->cookie("key") != md5(env("1C_PASSWORD"))) {
            echo "failure\n";
            echo "session error\n";
            exit;
        }
    }
    private function catalogFile()
    {
        $this->checkAccess();

        $filename = request()->input('filename');
        if (!isset($filename)) {
            echo "failure\n";
            echo "No filename variable\n";
            exit;
        }

        if (Str::position($filename, 'import_files') !== false) {
            $upload_file = Storage::path("images/{$filename}");
        } else {
            $upload_file = Storage::path("cache/exchange/{$filename}");
        }

        File::exists(dirname($upload_file)) or File::makeDirectory(dirname($upload_file), 0755, true);

        $data = file_get_contents("php://input");

        if ($data !== false) {
            if (File::put($upload_file, $data)) {
                echo "success\n";
                chmod($upload_file, 0755);
                exit;
            } else {
                echo "failure\n";
                echo "Can't open file: $upload_file\n";
                exit;
            }
        }
        echo "failure\n";
        echo "No data file\n";
    }

    private function manual()
    {
        $this->catalogImport();
    }

    private function catalogImport()
    {
        // $this->checkAccess();

        $filename = request()->input("filename");

        if (!isset($filename)) {
            echo "failure\n";
            echo "no filename parameter\n";
            exit;
        }

        if (strpos($filename, 'import') !== false) {
            $this->parseImport($filename);
            echo "success\n";
        } else if (strpos($filename, 'offers') !== false) {
            $this->parseOffers($filename);
            echo "success\n";
        } else {
            echo "failure\n";
            echo $filename;
        }
    }

    private function parseImport($filename)
    {
        $filename = Storage::path("cache/exchange/{$filename}");
        if (!File::exists($filename)) {
            echo "failure\n";
            echo "Filename {$filename} not found\n";
            exit;
        }

        $this->categories = Category::query()->get(['id', 'name', 'uuid', 'parent_id'])->mapWithKeys(function ($item) {
            return [$item->uuid => $item];
        });

        $this->properties = Property::query()->get(['id', 'uuid', 'name'])->mapWithKeys(function ($item) {
            return [$item->uuid => $item];
        });

        $this->values = PropertyValue::query()->get(['id', 'uuid', 'value'])->mapWithKeys(function ($item) {
            return [$item->uuid => $item];
        });

        $this->filter_groups = FilterGroup::query()->get(['id', 'uuid', 'name'])->mapWithKeys(function ($item) {
            return [$item->uuid => $item];
        });

        $xml = simplexml_load_file($filename);

        $this->parseCategories($xml->Классификатор->Группы);
        $this->parseProperties($xml->Классификатор->Свойства);
        $this->parseProducts($xml->Каталог->Товары);

        if (app()->environment(['production'])) {
            File::delete($filename);
        }
    }

    private function parseCategories($xml, $parent_id = null)
    {
        if ($xml?->Группа) {
            foreach ($xml->Группа as $group) {
                $uuid = (string) $group->Ид;
                $name = (string) $group->Наименование;
                $category = Category::updateOrCreate(
                    ['uuid' => $uuid],
                    ['name' => $name, 'parent_id' => $parent_id]
                );

                $this->categories[$uuid] = $category;

                if (isset($group->Группы)) {
                    $this->parseCategories($group->Группы, $category->id);
                }
            }
        }
    }

    private function parseProperties($xml)
    {
        if ($xml?->Свойство) {
            foreach ($xml->Свойство as $prop) {
                $uuid = (string) $prop->Ид;
                $name = (string) $prop->Наименование;

                $property = Property::updateOrCreate(['uuid' => $uuid], ['name' => $name]);
                $filter_groups = FilterGroup::updateOrCreate(['uuid' => $uuid], ['name' => $name]);

                $this->properties[$uuid] = $property;
                $this->filter_groups[$uuid] = $filter_groups;

                if ((string) $prop->ВариантыЗначений && (string) $prop->ТипЗначений == "Справочник") {
                    foreach ($prop->ВариантыЗначений->Справочник as $variant) {
                        if ((string) $variant->Значение != '') {
                            $variant_uuid = (string) $variant->ИдЗначения;
                            $variant_value = (string) $variant->Значение;

                            $value = PropertyValue::updateOrCreate(['uuid' => $variant_uuid], ['value' => $variant_value]);

                            $this->values[$variant_uuid] = $value;
                        }
                    }
                }
            }
        }
    }

    private function getBrand($name)
    {
        if (empty(trim($name))) {
            return null;
        }
        if (key_exists($name, $this->brands)) {
            return $this->brands[$name];
        }
        $brand = Brand::firstOrCreate(['name' => $name]);

        $this->brands[$name] = $brand;
        return $brand;
    }

    private function parseProducts($xml)
    {
        if ($xml->Товар) {
            foreach ($xml->Товар as $product) {
                $uuid = explode('#', (string) $product->Ид)[0];

                if (isset($product['Статус']) && $product['Статус'] == "Удален") {
                    Product::where('uuid', $uuid)->delete();
                    continue;
                }

                $model = (string) $product->Артикул;
                $sku = (string) $product->Код;
                $name = (string) $product->Наименование;
                $description = (string) $product->Описание;
                $brand = null;
                $category = null;
                $images = [];
                if ($product->Картинка) {
                    foreach ($product->Картинка as $img) {
                        $images[] = (string) $img;
                    }
                }

                // dump($uuid, $model, $sku, $name, $description, $images);

                $properties = [];
                $filters = [];
                $filter_groups = [];

                if ($product->Группы->Ид) {
                    $category = $this->categories[(string) $product->Группы->Ид[0]];
                }

                if ($product->ЗначенияРеквизитов->ЗначениеРеквизита) {
                    foreach ($product->ЗначенияРеквизитов->ЗначениеРеквизита as $rekvizit) {
                        switch ((string) $rekvizit->Наименование) {
                            case 'СЦентр_Производитель':
                            case 'Производитель':
                                $brand = $this->getBrand((string) $rekvizit->Значение);
                                break;

                            default:
                                # code...
                                break;
                        }
                    }
                }

                if ($product->ЗначенияСвойств) {
                    $n = 0;
                    foreach ($product->ЗначенияСвойств->ЗначенияСвойства as $prop) {
                        $value = (string) $prop->Значение;
                        if (empty($value)) {
                            continue;
                        }

                        Arr::exists($this->values, $value) and $value = $this->values[$value]['value'];

                        $value == "true" and $value = "Есть";

                        $properties[$this->properties[(string) $prop->Ид]->id] = [
                            'value' => $value,
                            'position' => $n++,
                        ];

                        $filter_group = FilterGroup::updateOrCreate(['uuid' => (string) $prop->Ид], ['name' => $this->properties[(string) $prop->Ид]->name]);
                        $filter_groups[$filter_group->id] = $filter_group->id;
                        $filter = Filter::firstOrCreate(['filter_group_id' => $filter_group->id, 'value' => $value]);
                        $filters[] = $filter->id;
                    }
                }

                $product = Product::where('uuid', $uuid)->orWhere('sku', $sku)->orWhere('name', $name)->updateOrCreate([], [
                    'uuid' => $uuid,
                    'model' => $model,
                    'sku' => $sku,
                    'name' => $name,
                    'image' => array_shift($images),
                    'description' => $description,
                    'category_id' => $category?->id,
                    'brand_id' => $brand?->id,
                ]);

                if ($images) {
                    $product->images()->delete();
                    foreach ($images as $key => $img) {
                        $product->images()->create([
                            'image' => $img,
                            'position' => $key,
                        ]);
                    }
                }

                if ($properties) {
                    $product->properties()->sync($properties);
                }

                if ($filters) {
                    $product->filters()->sync($filters);
                }

                if (isset($category)) {
                    $category->filters()->syncWithoutDetaching($filter_groups);
                }
            }
        }
    }

    private function parseOffers($filename)
    {
        $filename = Storage::path("cache/exchange/{$filename}");
        if (!File::exists($filename)) {
            echo "failure\n";
            echo "Filename {$filename} not found\n";
            exit;
        }

        $xml = simplexml_load_file($filename);
        $stores = [];
        if ($xml->ПакетПредложений->Склады->Склад) {
            foreach ($xml->ПакетПредложений->Склады->Склад as $store) {
                $uuid = (string) $store->Ид;
                $name = (string) $store->Наименование;
                $address = (string) $store->Адрес->Представление;
                $phone = null;
                if ($store->Контакты) {
                    foreach ($store->Контакты->Контакт as $contact) {
                        if ((string) $contact->Тип === "Телефон рабочий") {
                            $phone = (string) $contact->Значение;
                            break;
                        }
                    }
                }
                $store = Store::firstOrCreate([
                    'uuid' => $uuid,
                ], [
                    'name' => $name,
                    'address' => $address,
                    'phone' => $phone,
                ]);
                $stores[$uuid] = $store;
            }
        }
        foreach ($xml->ПакетПредложений->Предложения->Предложение as $offer) {
            $product = Product::query()->where('uuid', explode('#', (string) $offer->Ид)[0])->first();
            if ($product) {
                $product->update([
                    'price' => (int) $offer->Цены->Цена[0]->ЦенаЗаЕдиницу,
                    'quantity' => (int) $offer->Количество,
                ]);
                $qty = [];
                foreach ($offer->Склад as $store) {
                    $qty[$stores[(string) $store['ИдСклада']]->id] = ['quantity' => (string) $store['КоличествоНаСкладе']];
                }
                $product->stores()->sync($qty);
            }
        }
        if (app()->environment(['production'])) {
            File::delete($filename);
        }
    }
}
