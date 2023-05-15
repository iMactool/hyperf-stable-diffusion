<?php

declare(strict_types=1);
/**
 * This file is part of the imactool/hyperf-stable-diffusion.
 *
 * (c) imactool <chinauser1208@gmail.come>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
return [
    'url' => env('REPLICATE_URL', 'https://api.replicate.com/v1/predictions'),
    'token' => env('REPLICATE_TOKEN'),
    'version' => env('REPLICATE_STABLEDIFFUSION_VERSION', 'db21e45d3f7023abc2a46ee38a23973f6dce16bb082a930b0c49861f96d1e5bf'),
];
