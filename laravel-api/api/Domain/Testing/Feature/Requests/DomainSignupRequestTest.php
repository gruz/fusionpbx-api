<?php

namespace Api\Domain\Testing\Feature\Requests;

use Faker\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Infrastructure\Testing\TestCase;
use Api\Domain\Requests\DomainSignupRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Output\TrimmedBufferOutput;

class DomainSignupRequestTest extends TestCase
{

    /** @var DomainSignupRequest */
    private $rules;

    /** @var Validator */
    private $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = app()->get('validator');

        $this->rules = (new DomainSignupRequest())->rules();
    }

    public function validationProvider()
    {
        /* WithFaker trait doesn't work in the dataProvider */
        $faker = Factory::create(Factory::DEFAULT_LOCALE);

        return [
            'request_should_fail_when_no_title_is_provided' => [
                'passed' => true,
                'data' => [
                    'domain_name' => 'aaa.com',
                    'domain_namea' => 'a',
                ]
            ],
        ];
        [
            'request_should_fail_when_no_title_is_provided' => [
                'passed' => false,
                'data' => [
                    'price' => $faker->numberBetween(1, 50)
                ]
            ],
            'request_should_fail_when_no_price_is_provided' => [
                'passed' => false,
                'data' => [
                    'title' => $faker->word()
                ]
            ],
            'request_should_fail_when_title_has_more_than_50_characters' => [
                'passed' => false,
                'data' => [
                    'title' => $faker->paragraph()
                ]
            ],
            'request_should_pass_when_data_is_provided' => [
                'passed' => true,
                'data' => [
                    'title' => $faker->word(),
                    'price' => $faker->numberBetween(1, 50)
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider validationProvider
     * @param bool $shouldPass
     * @param array $mockedRequestData
     */
    public function validation_results_as_expected($shouldPass, $mockedRequestData)
    {
        $this->assertEquals(
            $shouldPass,
            $this->validate($mockedRequestData)
        );
    }

    protected function validate($mockedRequestData)
    {
        try {
            $validator = $this->validator->make($mockedRequestData, $this->rules);
            if ($result = $validator->validate()) {
                return true;
            }

            //code...
        } catch (\Throwable $th) {
            $errors = $validator->errors();
            foreach ($errors->getMessages() as $key => $messages) {
                // echo $messages;
                // dd($key, $messages);
                foreach ($messages as $message) {
                    // $this->assertEquals(false, true, $key . ' : ' . $message);
                    $this->writeLn($message, $key);
                }
            }


            // dd($errors->all());
            return false;
        }
        // dd($errors->all());
        exit;
        return $this->validator
            ->make($mockedRequestData, $this->rules)
            ->passes();
    }

    private function writeLn($message, $key = '') {
        fwrite(STDOUT, PHP_EOL . " \033[41m >> \033[1m" . $key . " \033[0m" . ' : ' . "\033[1m" . $message . "\033[0m" . PHP_EOL);
    }
}
