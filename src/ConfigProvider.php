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

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'stable-diffusion',
                    'description' => 'Publish config for StableDiffusion.',
                    'source' => __DIR__ . '/../publish/stable-diffusion.php',
                    'destination' => BASE_PATH . '/config/autoload/stable-diffusion.php',
                ],
                [
                    'id' => 'stable-diffusion-migrations',
                    'description' => 'Publish migrations for StableDiffusion',
                    'source' => __DIR__ . '/../publish/migrations/create_stable_diffusion_results_table.php',
                    'destination' => BASE_PATH . '/migrations/' . date('Y_m_d_His', time()) . '_create_stable_diffusion_results_table.php',
                ],
            ],
        ];
    }
}
