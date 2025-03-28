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
    use Hyperf\Codec\Json;
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

        private function __construct(protected $apiVersion)
        {
            $class = '\\Imactool\\HyperfStableDiffusion\\Uri\\' . $apiVersion;
            if (! class_exists($class)) {
                throw new Exception("Class {$class} not found");
            }
            $this->apiBase = ApplicationContext::getContainer()->get($class);
        }

        // 获取( https://stablediffusionapi.com)支持的已被本SDK适配的API集合
        public function supportApi(string $api = ''): array|string
        {
            $aiApis = [
                'StableDiffusionApiV3', // Stable Diffusion V3 APIs comes with below features https://documenter.getpostman.com/view/18679074/2s83zdwReZ#c7e3c6a0-b57d-4d17-ad5a-c4eb8571021f
                'DreamboothApiV4', // [Beta] DreamBooth API https://documenter.getpostman.com/view/18679074/2s83zdwReZ#27db9713-6068-41c2-8431-ada0d08d3cd5
                'EnterpriseApiV1', // [企业] Enterprise API  https://stablediffusionapi.com/docs/enterprise-plan/overview
            ];

            if ($api) {
                if (in_array($api, $aiApis)) {
                    return $aiApis[$api];
                }
                throw new Exception('无效 ' . $api . '。仅支持这些API' . Json::encode($aiApis));
            }

            return $aiApis;
        }

        public static function make(string $witchAPi = 'StableDiffusionApiV3'): self
        {
            return new StableDiffusion($witchAPi);
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
           $payloadArr = $this->payload;
           if (isset($payloadArr['prompt'])) {
               unset($payloadArr['prompt']);
           }
           $payload = '';
           $payload .= ', ' . implode(', ', array_values(array_unique($payloadArr)));
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
                $this->payload['request_id'] = $id;
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

        public function controlnet()
        {
            if (empty($this->payload)) {
                throw new Exception('Invalid payload. @see https://stablediffusionapi.com/docs/controlnet-main/');
            }

            $response = $this->client()->post(
                'https://modelslab.com/api/v5/controlnet',
                [
                    'json' => $this->payload,
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
//            $this->saveResult($result, $this->apiBase->text2imgUrl());
        }

        private function saveResult($result, $url)
        {
            if (Arr::get($result, 'status') !== 'error') {
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
        }

        private function client(): ClientInterface
        {
            return ApplicationContext::getContainer()->get(ClientFactory::class)->create([
                'timeout' => 600,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }
