<?php

namespace Database\Factories;

use App\Models\Voicemail;
use Illuminate\Database\Eloquent\Factories\Factory;


class VoicemailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Voicemail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $return = [
            "voicemail_password" => $this->faker->randomNumber(5, true),
            "greeting_id" => 0,
            "voicemail_alternate_greet_id" => 0,
            "voicemail_mail_to" => $this->faker->email,
            "voicemail_sms_to" => $this->faker->phoneNumber,
            "voicemail_transcription_enabled" => $this->faker->randomElement(["true", "false"]),
            "voicemail_attach_file" => "/path/to/file",
            "voicemail_file" => $this->faker->randomElement(["", "link", "attach"]),
            "voicemail_local_after_email" => $this->faker->randomElement(["true", "false"]),
            "voicemail_enabled" => $this->faker->randomElement(["true", "false"]),
            "voicemail_description" => $this->faker->text(),
            // // "voicemail_name_base64" => "string",
            "voicemail_tutorial" => "string"
        ];

        return $return;
    }
}
