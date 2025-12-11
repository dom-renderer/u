<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Str;

class GenerateStoreUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:store-phones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        \DB::beginTransaction();

        try {
            foreach (Store::all() as $store) {
                $user = User::where('phone_number', $store->mobile);

                if (empty($store->mobile)) {
                    $this->warn("Store {$store->name} has no moblie number");
                    continue;
                }

                if (empty($store->code)) {
                    $this->warn("Store {$store->name} has no code");
                    continue;
                }

                if ($user->exists()) {
                    $this->error("User with phone number {$store->mobile} already exists");
                    continue;
                }

                $user = $user->where('employee_id', "SP{$store->code}");

                if ($user->exists()) {
                    $this->error("User with employee id SP{$store->code} already exists");
                    continue;
                }

                $user = User::create([
                    'name' => $store->name,
                    'email' => $store->email ?? (Str::slug($store->name) . '@teapost.com'),
                    'phone_number' => $store->mobile,
                    'employee_id' => "SP{$store->code}",
                    'username' => $store->mobile,
                    'status' => 1,
                    'password' => $store->mobile
                ]);

                $user->syncRoles([Helper::$roles['store-phone']]);

                $this->info("NEW STORE PHONE USER SP{$store->code} GENERATED");
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            echo $e->getMessage() . " ON LINE " . $e->getLine();
        }
    }
}
