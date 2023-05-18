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

    class NegativePrompt
    {
        public function __construct(
            protected string $negative_prompt = ''
        ) {
        }

        public static function make(): self
        {
            return new NegativePrompt();
        }

        public function with(string $negative_prompt): static
        {
            $this->negative_prompt = $negative_prompt;
            return $this;
        }

        public function userNegativePrompt(): string
        {
            return $this->negative_prompt;
        }
    }
