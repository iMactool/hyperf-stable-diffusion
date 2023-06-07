<?php

declare(strict_types=1);
/**
 * This file is part of the imactool/hyperf-stable-diffusion.
 *
 * (c) imactool <chinauser1208@gmail.come>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Imactool\HyperfStableDiffusion\Uri;

class EnterpriseApiV1
{
    public const ORIGIN = 'https://stablediffusionapi.com/api';

    public const API_VERSION = 'v1';

    public const OPEN_AI_URL = self::ORIGIN . '/' . self::API_VERSION;

    public function text2imgUrl(): string
    {
        return self::OPEN_AI_URL . '/enterprise/text2img';
    }

    public function img2imgUrl(): string
    {
        return self::OPEN_AI_URL . '/enterprise/img2img';
    }

    public function inpaintUrl(): string
    {
        return self::OPEN_AI_URL . '/enterprise/inpaint';
    }
}
