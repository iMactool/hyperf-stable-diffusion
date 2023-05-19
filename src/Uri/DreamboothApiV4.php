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

    class DreamboothApiV4
    {
        public const ORIGIN = 'https://stablediffusionapi.com/api';

        public const API_VERSION = 'v4';

        public const OPEN_AI_URL = self::ORIGIN . '/' . self::API_VERSION;

        public function text2imgUrl(): string
        {
            return self::OPEN_AI_URL . '/dreambooth';
        }

        public function img2imgUrl(): string
        {
            return self::OPEN_AI_URL . '/dreambooth/img2img';
        }

        public function inpaintUrl(): string
        {
            return self::OPEN_AI_URL . '/dreambooth/inpaint';
        }

        public function text2VideoUrl(): string
        {
            return self::OPEN_AI_URL . '/text2video';
        }

        public function fetchUrl(): string
        {
            return self::OPEN_AI_URL . '/dreambooth/fetch';
        }

        public function modelReloadUrl(): string
        {
            return self::OPEN_AI_URL . '/dreambooth/model_reload';
        }
    }
