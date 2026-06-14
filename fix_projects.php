<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\Project::where('id', 1)->update(['name' => 'Dự án Hóc Môn']);
App\Models\Project::where('id', 2)->update(['name' => 'Dự án Hậu Nghĩa']);
App\Models\Project::where('id', 3)->update(['name' => 'Dự án Cần Giờ']);
App\Models\Project::where('id', 4)->update(['name' => 'Dự án Số 4']);

$hr = App\Models\Project::updateOrCreate(
    ['id' => 5], 
    ['name' => 'Ngôi nhà HR', 'code' => 'HR', 'status' => 'active', 'description' => 'Trung tâm điều hành và phân quyền']
);

$admin = App\Models\User::where('phone', '0708091050')->first();
if($admin) {
    $houses = is_array($admin->allowed_houses) ? $admin->allowed_houses : [];
    if(!in_array(5, $houses)) {
        $houses[] = 5;
        $admin->allowed_houses = $houses;
        $admin->save();
    }
}
echo "Done\n";
