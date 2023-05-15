<?php

    declare(strict_types=1);
/**
 * This file is part of the imactool/hyperf-stable-diffusion.
 *
 * (c) imactool <chinauser1208@gmail.come>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Imactool\HyperfStableDiffusion;

    use Exception;
    use Hyperf\Collection\Arr;
    use Hyperf\Context\ApplicationContext;
    use Hyperf\Guzzle\ClientFactory;
    use Imactool\HyperfStableDiffusion\Models\StableDiffusionResult;
    use Psr\Http\Client\ClientInterface;

    class StableDiffusion
    {
        private function __construct(
            public ?Prompt $prompt = null,
            private int $width = 512,
            private int $height = 512
        ) {
        }

        public static function make(): self
        {
            return new self();
        }

        public static function get(string $replicateId)
        {
            $result = StableDiffusionResult::query()->where('replicate_id', $replicateId)->first();
            assert($result !== null, 'Unknown id');
            $idleStatuses = ['starting', 'processing'];
            if (! in_array($result->status, $idleStatuses)) {
                return $result;
            }

            $response = self::make()
                ->client()
                ->get($result->url);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to retrieve data.');
            }

            $responseData = json_decode((string) $response->getBody(), true);

            $result->status = Arr::get($responseData, 'status', $result->status);
            $result->output = Arr::has($responseData,'output') ? Arr::get($responseData, 'output') : null;
            $result->error = Arr::get($responseData, 'error');
            $result->predict_time = Arr::get($responseData, 'metrics.predict_time');
            $result->save();

            return $result;
        }

        public function withPrompt(Prompt $prompt)
        {
            $this->prompt = $prompt;
            return $this;
        }

        public function width(int $width)
        {
            assert($width > 0, 'Width must be greater than 0');
            if ($width <= 768) {
                assert($width <= 768 && $this->width <= 1024, 'Width must be lower than 768 and height lower than 1024');
            } else {
                assert($width <= 1024 && $this->width <= 768, 'Width must be lower than 1024 and height lower than 768');
            }
            $this->width = $width;
            return $this;
        }

        public function height(int $height)
        {
            assert($height > 0, 'Height must be greater than 0');
            if ($height <= 768) {
                assert($height <= 768 && $this->width <= 1024, 'Height must be lower than 768 and width lower than 1024');
            } else {
                assert($height <= 1024 && $this->width <= 768, 'Height must be lower than 1024 and width lower than 768');
            }

            $this->height = $height;

            return $this;
        }

        public function generate(int $numberOfImages)
        {
            assert($this->prompt !== null, 'You must provide a prompt');
            assert($numberOfImages > 0, 'You must provide a number greater than 0');

            $response = $this->client()->post(
                config('stable-diffusion.url'),
                [
                    'json' => [
                        'version' => config('stable-diffusion.version'),
                        'input' => [
                            'prompt' => $this->prompt->toString(),
                            'num_outputs' => $numberOfImages,
                        ],
                    ],
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);

            $data = [
                'replicate_id' => $result['id'],
                'user_prompt' => $this->prompt->userPrompt(),
                'full_prompt' => $this->prompt->toString(),
                'url' => $result['urls']['get'],
                'status' => $result['status'],
                'output' => isset($result['output']) ? $result['output'] : null,
                'error' => $result['error'],
                'predict_time' => null,
            ];

	        try {
		        StableDiffusionResult::create($data);
	        }catch (\Exception $exception){
//		        $msg = $exception->getMessage();
		        if ($exception instanceof \PDOException) {
			        $errorInfo = $exception->errorInfo;
			        $code = $errorInfo[1];
//			        $sql_state = $errorInfo[0];
//			        $msg = isset($errorInfo[2]) ? $errorInfo[2] : $sql_state;
		        }
		        if ((int) $code !== 1062) {
			        return $result;
		        }
	        }

            return $result;
        }

        private function client(): ClientInterface
        {
            return ApplicationContext::getContainer()->get(ClientFactory::class)->create([
                'base_uri' => config('stable-diffusion.base_uri'),
                //                'timeout' => 10,
                'headers' => [
                    'Authorization' => 'Token ' . config('stable-diffusion.token'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }
