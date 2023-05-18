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

    use Imactool\HyperfStableDiffusion\Traits\HasAuthors;
    use Imactool\HyperfStableDiffusion\Traits\HasCanvases;
    use Imactool\HyperfStableDiffusion\Traits\HasFinishingTouches;
    use Imactool\HyperfStableDiffusion\Traits\HasPaintingStyles;

    class Prompt
    {
        use HasPaintingStyles;
        use HasAuthors;
        use HasCanvases;
        use HasFinishingTouches;

        private function __construct(
            protected string $prompt = '',
            protected ?string $paintingStyle = null,
            protected ?string $author = null,
            protected ?string $canvas = null,
            protected array $finishingTouches = [],
        ) {
        }

        public static function make(): Prompt
        {
            return new Prompt();
        }

        public function with(string $prompt): static
        {
            $this->prompt = $prompt;
            return $this;
        }

        public function toString($inputParams = []): string
        {
            $prompt = $this->prompt;
            if ($this->author) {
                $prompt .= ', made by ' . $this->author;
            }

            if ($this->canvas) {
                $prompt = $this->canvas . ' of ' . $prompt;
            }

            if ($this->paintingStyle) {
                $prompt .= ', ' . $this->paintingStyle;
            }

            if (! empty($this->finishingTouches)) {
                $prompt .= ', ' . implode(', ', array_values(array_unique($this->finishingTouches)));
            }

            if (! empty($inputParams)) {
                $unsetKey = 'image';
                if (array_key_exists($unsetKey, $inputParams)) {
                    unset($inputParams[$unsetKey]);
                }
                $prompt .= ', ' . implode(', ', array_values(array_unique($inputParams)));
            }

            return $prompt;
        }

        public function userPrompt(): string
        {
            return $this->prompt;
        }
    }
