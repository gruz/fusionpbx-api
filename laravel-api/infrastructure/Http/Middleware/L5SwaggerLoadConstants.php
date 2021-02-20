<?php

namespace Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class L5SwaggerLoadConstants
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws L5SwaggerException
     */
    public function handle($request, Closure $next)
    {
        $pathInfos[] = '/' . config('l5-swagger.defaults.routes.docs') . '/' . config('l5-swagger.documentations.default.paths.docs_json');
        $pathInfos[] = '/' . config('l5-swagger.defaults.routes.docs') . '/' . config('l5-swagger.documentations.default.paths.docs_yaml');

        if (in_array($request->getPathInfo(), $pathInfos)) {
            $this->registerConstantsForSwaggerProcessor();
        }

        return $next($request);
    }

    private function registerConstantsForSwaggerProcessor()
    {
        static $isSecondRun = false;

        if ($isSecondRun) {
            return;
        }
        $isSecondRun = true;

        if (config('app.debug')) {
            $data = \Api\Settings\Models\Default_setting::get();
        } else {
            $data = Cache::remember(
                __METHOD__ . serialize(func_get_args()),
                now()->addDay(),
                \Api\Settings\Models\Default_setting::get()
            );
        }

        $categories = $data->groupBy('default_setting_category');

        // foreach ($categories as $key => $category) {
        //     $categories[$key] = $category->pluck('default_setting_subcategory')->toArray();
        // }

        // define('FPBX_DEFAULT_SETTINGS_X_CATEGORIES', $categories);

        // define('FPBX_DEFAULT_SETTINGS_CATEGORY', array_keys($data->groupBy('default_setting_category')->toArray()) );
        // define('FPBX_DEFAULT_SETTINGS_SUBCATEGORY', array_keys($data->groupBy('default_setting_subcategory')->toArray()));
        // define('FPBX_DEFAULT_SETTINGS_TYPE', array_keys($data->groupBy('default_setting_name')->toArray()));

        // dd(FPBX_DEFAULT_SETTINGS_CATEGORY, FPBX_DEFAULT_SETTINGS_SUBCATEGORY, FPBX_DEFAULT_SETTINGS_TYPE);

        foreach ($categories as $settingCategory => $settings) {
            $settings = $settings->toArray();

            define('FPBX_DEFAULT_SETTINGS_' . $settingCategory, array_column($settings, 'default_setting_subcategory'));
            define('FPBX_DEFAULT_SETTINGS_' . $settingCategory . '_FIELD_TYPES', array_column($settings, 'default_setting_name'));
        }
    }
}
