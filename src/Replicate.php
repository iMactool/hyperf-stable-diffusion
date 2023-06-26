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
    use PDOException;
    use Psr\Http\Client\ClientInterface;

    class Replicate
    {
        private static $platform = 'replicate';

        private array $inputParams = [];

        private string $baseUrl = '';

        private string $token = '';

        private string $version = '';

        private string $webhook = '';

        private function __construct(
            public ?Prompt $prompt = null,
            private int $width = 512,
            private int $height = 512
        ) {
        }

        public function converUrl(string $url): self
        {
            $this->baseUrl = $url;
            return $this;
        }

        public function getBaseUrl(): string
        {
            if (empty($this->baseUrl)) {
                $this->baseUrl = config('stable-diffusion.url');
            }
            return $this->baseUrl;
        }

        public function converToken(string $token): self
        {
            $this->token = $token;
            return $this;
        }

        public function getToken(): string
        {
            if (empty($this->token)) {
                $this->token = config('stable-diffusion.token');
            }
            return $this->token;
        }

        public function converVersion(string $version): self
        {
            $this->version = $version;
            return $this;
        }

        public function getVersion(): string
        {
            if (empty($this->version)) {
                $this->version = config('stable-diffusion.version');
            }
            return $this->version;
        }

        public static function make(): self
        {
            return new self();
        }

        public function getV2(string $replicateId)
        {
            $result = StableDiffusionResult::query()->where('replicate_id', $replicateId)->first();
            assert($result !== null, 'Unknown id');
            $idleStatuses = ['starting', 'processing'];
            if (! in_array($result->status, $idleStatuses)) {
                return $result;
            }

            $response = $this->client()->get($result->url);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to retrieve data.');
            }

            $responseData = json_decode((string) $response->getBody(), true);

            $result->status = Arr::get($responseData, 'status', $result->status);
            $result->output = Arr::has($responseData, 'output') ? Arr::get($responseData, 'output') : null;
            $result->error = Arr::get($responseData, 'error');
            $result->predict_time = Arr::get($responseData, 'metrics.predict_time');
            $result->save();

            return $result;
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
            $result->output = Arr::has($responseData, 'output') ? Arr::get($responseData, 'output') : null;
            $result->error = Arr::get($responseData, 'error');
            $result->predict_time = Arr::get($responseData, 'metrics.predict_time');
            $result->save();

            return $result;
        }

        public function list()
        {
            $response = self::make()
                ->client()
                ->get($this->getBaseUrl());

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to retrieve data.');
            }

            return json_decode((string) $response->getBody(), true);
        }

        public function getModel($model_owner, $model_name)
        {
            if (empty($model_name) || empty($model_owner)) {
                throw new Exception("model_owner or model_name can't be empty.");
            }
            $uri = 'https://api.replicate.com/v1/models/' . trim($model_owner) . '/' . trim($model_name);

            $response = self::make()
                ->client()
                ->get($uri);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to retrieve data.');
            }

            return json_decode((string) $response->getBody(), true);
        }

        public function getModelVersion($model_owner, $model_name, $version_id)
        {
            if (empty($model_name) || empty($model_owner) || empty($version_id)) {
                throw new Exception("model_owner、version_id or model_name can't be empty.");
            }
            $uri = 'https://api.replicate.com/v1/models/' . trim($model_owner) . '/' . trim($model_name) . '/versions/' . $version_id;

            $response = self::make()
                ->client()
                ->get($uri);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to retrieve data.');
            }

            return json_decode((string) $response->getBody(), true);
        }

        public function withPrompt(Prompt $prompt)
        {
            $this->prompt = $prompt;
            return $this;
        }

        /**
         * except prompt,other API parameters.
         *
         * @param string $key 参数本身
         * @param mixed $value 参数值
         *
         * @return $this
         */
        public function inputParams(string $key, mixed $value)
        {
            $this->inputParams[$key] = $value;
            return $this;
        }

        public function setWebHook(string $url)
        {
            $this->webhook = $url;
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

        public function generate()
        {
            assert($this->prompt !== null, 'You must provide a prompt');
//            assert($numberOfImages > 0, 'You must provide a number greater than 0');
            $input = [];
            $input = [
                'prompt' => $this->prompt->toString(),
                //                'num_outputs' => $numberOfImages,
            ];

            if (isset($this->inputParams['prompts'])) {
                unset($input['prompt']);
            }
            $input = array_merge($input, $this->inputParams);

            $json = [
                'version' => $this->getVersion(),
                'input' => $input,
            ];

            if (! empty($this->webhook)) {
                $json['webhook'] = $this->webhook;
            }

            $response = $this->client()->post(
                $this->getBaseUrl(),
                [
                    'json' => $json,
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);

            $data = [
                'replicate_id' => $result['id'],
                'platform' => self::$platform,
                'user_prompt' => $this->prompt->userPrompt(),
                'full_prompt' => $this->prompt->toString($this->inputParams),
                'url' => str_replace('https://api.replicate.com/v1/predictions', $this->getBaseUrl(), $result['urls']['get']),
                'status' => $result['status'],
                'output' => isset($result['output']) ? $result['output'] : null,
                'error' => $result['error'],
                'predict_time' => null,
            ];

            try {
                StableDiffusionResult::create($data);
            } catch (Exception $exception) {
                $msg = $exception->getMessage();
//                var_dump(['data insert error' => $msg]);
                if ($exception instanceof PDOException) {
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
                //                'base_uri' => $this->getBaseUrl(),
                'timeout' => 600,
                'headers' => [
                    'Authorization' => 'Token ' . $this->getToken(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }
