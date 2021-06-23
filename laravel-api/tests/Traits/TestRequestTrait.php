<?php

namespace Tests\Traits;

trait TestRequestTrait
{
    private $rules;

    /** @var Validator */
    private $validator;

    private $showDebugOutput = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = app()->get('validator');

        // $className = trim(get_class($this), 'Test');
        $className = get_class($this);
        $className = substr(get_class($this), 0, -4);
        $className = explode('\\',$className);
        $className = array_diff($className, ['Testing']);
        $className = implode('\\', $className);
        echo '<pre> Line: ' . __LINE__ . ' | ' . __FILE__ . PHP_EOL;
        print_r($className);
        echo PHP_EOL . '</pre>' . PHP_EOL;

        $this->rules = (new $className())->rules();

    }

    /**
     * @test
     * @dataProvider validationProvider
     * @param bool $shouldPass
     * @param array $mockedRequestData
     */
    public function validation_results_as_expected($shouldPass, $mockedRequestData)
    {
        if ($mockedRequestData instanceof \Closure) {
            $mockedRequestData = $mockedRequestData();
        }

        $message = null;
        list($result, $message) = $this->validate($mockedRequestData);
        $this->assertEquals($shouldPass, $result, $message);
    }

    protected function validate($mockedRequestData)
    {
        try {
            $validator = $this->validator->make($mockedRequestData, $this->rules);
            if ($result = $validator->validate()) {
                return [true, ''];
            }
        } catch (\Throwable $th) {
            $errors = $validator->errors();
            $msgs = [];

            $this->writeLn(PHP_EOL . 'All validation errors:' . PHP_EOL);
            foreach ($errors->getMessages() as $key => $messages) {
                $incomingData = \Illuminate\Support\Arr::get($mockedRequestData, $key, '[empty]');
                if (is_array($incomingData) || is_object($incomingData)) {
                    // $incomingData = print_r($incomingData);
                    $incomingData = collect($incomingData)->__toString();
                }
                foreach ($messages as $message) {
                    $line = "\033[0m \033[41m >> \033[1m" . $key . ' => ' . $incomingData . " \033[0m" . ' : ' . "\033[1m" . $message . "\033[0m" . PHP_EOL;
                    $this->writeLn($line);
                    $msgs[] = $line;
                }
            }

            return [false, implode(PHP_EOL, $msgs)];
        }
    }

    private function writeLn($message)
    {
        if ($this->showDebugOutput) {
            fwrite(STDOUT, $message);
        }
    }
}
