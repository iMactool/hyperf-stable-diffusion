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
    use Imactool\HyperfStableDiffusion\Uri\DreamboothApiV4;
    use Imactool\HyperfStableDiffusion\Uri\StableDiffusionApiV3;
    use Psr\Http\Client\ClientInterface;

    class StableDiffusion
    {
        private array $payload = [];

        private $apiBase;

        private static $platform = 'stablediffusionapi';

        private function __construct()
        {
        }

        public static function make(): self
        {
            return new self();
        }

        public function useStableDiffusionApiV3(): StableDiffusion
        {
            $this->apiBase = ApplicationContext::getContainer()->get(StableDiffusionApiV3::class);
            return $this;
        }

        public function useDreamboothApiV4(): StableDiffusion
        {
            $this->apiBase = ApplicationContext::getContainer()->get(DreamboothApiV4::class);
            return $this;
        }

       /**
        * API parameters.
        *
        * @param string $key 参数本身
        * @param mixed  $value 参数值
        *
        * @return $this
        */
       public function withPayload(string $key, mixed $value)
       {
           $this->payload[$key] = $value;
           return $this;
       }

       public function payloadArr2String(): string
       {
           $payload = '';
           $payload .= ', ' . implode(', ', array_values(array_unique($this->payload)));
           return $payload;
       }

        public function text2img()
        {
            if (empty($this->payload)) {
                throw new Exception('Invalid payload. @see https://stablediffusionapi.com/docs/');
            }

            $response = $this->client()->post(
                $this->apiBase->text2imgUrl(),
                [
                    'json' => $this->payload,
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);
            var_dump(['$result 请求结果' => $result]);
            $this->saveResult($result, $this->apiBase->text2imgUrl());
            return $result;
        }

        public function img2img()
        {
            if (empty($this->payload)) {
                throw new Exception('Invalid payload. @see https://stablediffusionapi.com/docs/');
            }

            $response = $this->client()->post(
                $this->apiBase->img2imgUrl(),
                [
                    'json' => $this->payload,
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);
            var_dump(['$result 请求结果' => $result]);
            $this->saveResult($result, $this->apiBase->img2imgUrl());
            return $result;
        }

        public function inpaint()
        {
            if (empty($this->payload)) {
                return throw new Exception('Invalid payload. @see https://stablediffusionapi.com/docs/');
            }

            $response = $this->client()->post(
                $this->apiBase->inpaintUrl(),
                [
                    'json' => $this->payload,
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);

            $this->saveResult($result, $this->apiBase->inpaintUrl());
            return $result;
        }

        public function fetch($id)
        {
            $result = StableDiffusionResult::query()->where('replicate_id', $id)->first();
            assert($result !== null, 'Unknown id');
            $idleStatuses = ['success'];
            if (in_array($result->status, $idleStatuses)) {
                return $result;
            }

            $url = '';
            if ($this->apiBase instanceof StableDiffusionApiV3) {
                $url = $this->apiBase->fetchUrl($id);
            }
            if ($this->apiBase instanceof DreamboothApiV4) {
                $url = $this->apiBase->fetchUrl();
            }

            $response = $this->client()->post(
                $url,
                [
                    'json' => $this->payload,
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            $result->status = Arr::get($responseData, 'status', $result->status);
            $result->output = Arr::has($responseData, 'output') ? Arr::get($responseData, 'output') : null;
            $result->error = Arr::has($responseData, 'error') ? Arr::get($responseData, 'error') : null;
            $result->save();
            return $result;
        }

        public function systemLoad()
        {
            if (! $this->apiBase instanceof StableDiffusionApiV3) {
                return throw new Exception('「' . $this->apiBase . '」并不是一个 StableDiffusionApiV3 实例');
            }

            $response = $this->client()->post(
                $this->apiBase->systemLoadUrl(),
                [
                    'json' => $this->payload,
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        }

          public function superResolution()
          {
              if (! $this->apiBase instanceof StableDiffusionApiV3) {
                  return throw new Exception('「' . $this->apiBase . '」并不是一个 StableDiffusionApiV3 实例');
              }

              $response = $this->client()->post(
                  $this->apiBase->superResolutionUrl(),
                  [
                      'json' => $this->payload,
                  ]
              );

              return json_decode($response->getBody()->getContents(), true);
          }

        private function saveResult($result, $url)
        {
            $data = [
                'replicate_id' => Arr::has($result, 'id') ? Arr::get($result, 'id') : 0,
                'platform' => self::$platform,
                'user_prompt' => isset($this->payload['prompt']) ? $this->payload['prompt'] : '',
                'full_prompt' => $this->payloadArr2String(),
                'url' => $url,
                'status' => Arr::has($result, 'status') ? Arr::get($result, 'status') : '',
                'output' => Arr::has($result, 'output') ? Arr::get($result, 'output') : '',
                'error' => Arr::has($result, 'error') ? Arr::get($result, 'error') : null,
                'predict_time' => Arr::has($result, 'generationTime') ? Arr::get($result, 'generationTime') : null,
            ];
            StableDiffusionResult::create($data);
        }

        private function client(): ClientInterface
        {
            return ApplicationContext::getContainer()->get(ClientFactory::class)->create([
                //                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }
