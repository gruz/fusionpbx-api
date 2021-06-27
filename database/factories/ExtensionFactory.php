<?php

namespace Database\Factories;

use App\Models\Extension;
use Illuminate\Database\Eloquent\Factories\Factory;


class ExtensionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Extension::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $usedExtensions = [];

        $i = 0;
        while (true) {
            $extension = $this->faker->numberBetween(config('fpbx.extension.min'), config('fpbx.extension.max'));
            if (!in_array($extension, $usedExtensions)) {
                $usedExtensions[] = $extension;
                break;
            }
            if ($i > 100) {
                break;
            }
            $i++;
        }

        $return = [
            "extension" => $extension,
            // "number_alias" => "string",
            "password" => $this->faker->password() . '0aA',
            "accountcode" => $this->faker->numerify('account-####'),
            "effective_caller_id_name" => $this->faker->name,
            "effective_caller_id_number" => $this->faker->numberBetween(100, 200),
            "outbound_caller_id_name" => $this->faker->name,
            "outbound_caller_id_number" => $this->faker->numberBetween(100, 200),
            "emergency_caller_id_name" => $this->faker->name,
            "emergency_caller_id_number" => $this->faker->numberBetween(100, 200),
            "directory_first_name" => $this->faker->name,
            "directory_last_name" => $this->faker->lastName,
            "directory_visible" => $this->faker->randomElement(["true", "false"]),
            "directory_exten_visible" => $this->faker->randomElement(["true", "false"]),
            "limit_max" => $this->faker->numberBetween(2, 7),
            "limit_destination" => "error/user_busy",
            "missed_call_app" => "string",
            "missed_call_data" => "string",
            // "user_context" => "string",
            "toll_allow" => $this->faker->randomElement(['domestic', 'international', 'local']),
            "call_timeout" => 30,
            "call_group" => $this->faker->randomElement(['sales', 'support', 'billing']),
            "call_screen_enabled" => $this->faker->randomElement(["true", "false"]),
            "user_record" => $this->faker->randomElement(['', 'all', 'local', 'inbound', 'outbound']),
            "hold_music" => $this->faker->randomElement(['', "local_stream://default"]),
            "auth_acl" => "string",
            "cidr" => "string",
            "sip_force_contact" => $this->faker->randomElement([
                "",
                "NDLB-connectile-dysfunction",
                "NDLB-connectile-dysfunction-2.0",
                "NDLB-tls-connectile-dysfunction",
            ]),
            "nibble_account" => null,
            "sip_force_expires" => null,
            "mwi_account" => $this->faker->email,
            "sip_bypass_media" => $this->faker->randomElement([
                "",
                "bypass-media",
                "bypass-media-after-bridge",
                "proxy-media",
            ]),
            "unique_id" => null,
            "dial_string" => '/location/of/the/endpoint',
            "dial_user" => null,
            "dial_domain" => null,
            "do_not_disturb" => null,
            "forward_all_destination" => null,
            "forward_all_enabled" => null,
            "forward_busy_destination" => null,
            "forward_busy_enabled" => null,
            "forward_no_answer_destination" => null,
            "forward_no_answer_enabled" => null,
            "forward_user_not_registered_destination" => null,
            "forward_user_not_registered_enabled" => null,
            "follow_me_uuid" => null,
            "forward_caller_id_uuid" => null,
            "follow_me_enabled" => "string",
            "follow_me_destinations" => "string",
            "enabled" => $this->faker->randomElement(["true", "false"]),
            "description" => 'Extension created while testing API',
            "absolute_codec_string" => 'absolute/codec/string',
            "force_ping" => $this->faker->randomElement(["", "true", "false"]),
        ];

        return $return;
    }
}
