# imactool/hyperf-stable-diffusion

> 基于 `rulilg/laravel-stable-diffusion` 包直接平移并适配了 hyperf 框架。

> 我对它进行了一些改造，大部分功能保持了相同。在这里感谢一下 RuliLG ，实现了如此强大好用的 stable-diffusion 组件。

基于 Replicate API 和 stablediffusionapi 的 Stable Diffusion 实现。
- 🎨 Built-in prompt helper to create better images
- 🚀 Store the results in your database
- 🎇 Generate multiple images in the same API call
- 💯 Supports both (text to image) and (image to image)


鸣谢：原作：[RuliLG](https://github.com/RuliLG)，特此鸣谢!

## 安装

```
composer require imactool/hyperf-stable-diffusion

```
注意 表新增了`platform`平台字段。-- 后续会使用迁移增加，目前不考虑 :)

## 发布配置（包含配置文件和迁移文件)

```
php bin/hyperf.php vendor:publish imactool/hyperf-stable-diffusion
php bin/hyperf.php migrate
```
至此，配置完成。

```php
return [
    'url' => env('REPLICATE_URL', 'https://api.replicate.com/v1/predictions'),
    'token' => env('REPLICATE_TOKEN'),
    'version' => env('REPLICATE_STABLEDIFFUSION_VERSION', 'db21e45d3f7023abc2a46ee38a23973f6dce16bb082a930b0c49861f96d1e5bf'),
];

```

然后在 `.env` 里增加 [Replicate](https://replicate.com/) 的 `token` 到 `REPLICATE_TOKEN`即可。

## 使用

### 文字生成图片（Text to Image)
```php
use Imactool\HyperfStableDiffusion\Prompt;
use Imactool\HyperfStableDiffusion\Replicate;

   $result = Replicate::make()->withPrompt(
            Prompt::make()
                ->with('a panda sitting on the streets of New York after a long day of walking')
                ->photograph()
                ->resolution4k()
                ->trendingOnArtStation()
                ->highlyDetailed()
                ->dramaticLighting()
                ->octaneRender()
        )->generate(3);
```

### 图片生成图片(Image to Image)
```php
use Imactool\HyperfStableDiffusion\Prompt;
use Imactool\HyperfStableDiffusion\Replicate;
use Intervention\Image\ImageManager;

//这里使用了 intervention/image 扩展来处理图片文件，你也可以更换为其他的
 $sourceImg =  (string) (new ImageManager(['driver' => 'imagick']))->make('path/image/source.png')->encode('data-url');

$prompt = 'Petite 21-year-old Caucasian female gamer streaming from her bedroom with pastel pink pigtails and gaming gear. Dynamic and engaging image inspired by colorful LED lights and the energy of Twitch culture, in 1920x1080 resolution.';
$result = Replicate::make()
    ->converVersion('a991dcab77024471af6a89ef758d98d1a54c5a25fc52a06ccfd7754b7ad04b35')
    ->withPrompt(
        Prompt::make()
            ->with($prompt)
    )
    ->inputParams('image',$sourceImg)
    ->inputParams('negative_prompt', 'disfigured, kitsch, ugly, oversaturated, greain, low-res, Deformed, blurry, bad anatomy, disfigured, poorly drawn face, mutation, mutated, extra limb, ugly, poorly drawn hands, missing limb, blurry, floating limbs, disconnected limbs, malformed hands, blur, out of focus, long neck, long body, ugly, disgusting, poorly drawn, childish, mutilated, mangled, old, surreal, calligraphy, sign, writing, watermark, text, body out of frame, extra legs, extra arms, extra feet, out of frame, poorly drawn feet, cross-eye, blurry, bad anatomy')
    ->inputParams('strength', 0.5)
    ->inputParams('upscale', 2)
    ->inputParams('num_inference_steps', 25)
    ->inputParams('guidance_scale', 7.5)
    ->inputParams('scheduler', 'EulerAncestralDiscrete')
    ->generate(1);
```


### 查询结果

```php
use Imactool\HyperfStableDiffusion\Replicate;
 $freshResults = Replicate::get($replicate_id);

```


## Generating prompts

There are several styles already built-in:

Method | Prompt modification
---- | ----
`realistic()` | {prompt}, realistic
`hyperrealistic()` | {prompt}, hyperrealistic
`conceptArt()` | {prompt}, concept art
`abstractArt()` | {prompt}, abstract art
`oilPainting()` | {prompt}, oil painting
`watercolor()` | {prompt}, watercolor
`acrylic()` | {prompt}, acrylic
`pencilDrawing()` | {prompt}, pencil drawing
`digitalPainting()` | {prompt}, digital painting
`penDrawing()` | {prompt}, pen drawing
`charcoalDrawing()` | {prompt}, charcoal drawing
`byPicasso()` | {prompt}, by Pablo Picasso
`byVanGogh()` | {prompt}, by Vincent Van Gogh
`byRembrandt()` | {prompt}, by Rembrandt
`byMunch()` | {prompt}, by Edvard Munch
`byKlimt()` | {prompt}, by Paul Klimt
`byKandinsky()` | {prompt}, by Jackson Pollock
`byMonet()` | {prompt}, by Claude Monet
`byDali()` | {prompt}, by Salvador Dali
`byDegas()` | {prompt}, by Edgar Degas
`byKahlo()` | {prompt}, by Frida Kahlo
`byCezanne()` | {prompt}, by Pablo Cezanne
`photograph()` | a photo of {prompt}
`highlyDetailed()` | {prompt}, highly detailed
`surrealism()` | {prompt}, surrealism
`trendingOnArtStation()` | {prompt}, trending on art station
`triadicColorScheme()` | {prompt}, triadic color scheme
`smooth()` | {prompt}, smooth
`sharpFocus()` | {prompt}, sharp focus
`matte()` | {prompt}, matte
`elegant()` | {prompt}, elegant
`theMostBeautifulImageEverSeen()` | {prompt}, the most beautiful image ever seen
`illustration()` | {prompt}, illustration
`digitalPaint()` | {prompt}, digital paint
`dark()` | {prompt}, dark
`gloomy()` | {prompt}, gloomy
`octaneRender()` | {prompt}, octane render
`resolution8k()` | {prompt}, 8k
`resolution4k()` | {prompt}, 4k
`washedColors()` | {prompt}, washed colors
`sharp()` | {prompt}, sharp
`dramaticLighting()` | {prompt}, dramatic lighting
`beautiful()` | {prompt}, beautiful
`postProcessing()` | {prompt}, post processing
`pictureOfTheDay()` | {prompt}, picture of the day
`ambientLighting()` | {prompt}, ambient lighting
`epicComposition()` | {prompt}, epic composition

Additionally, you can add custom styles with the following methods:

- `as(string $canvas)`: to add a string at the beginning (i.e. "a photograph of")
- `paintingStyle(string $style)`: to add a painting style (i.e. realistic, hiperrealistic, etc.)
- `by(string $author)`: to instruct the system to paint it with the style of a certain author
- `effect(string $effect)`: to add a finishing touch to the prompt. You can add as many as you want.

To learn more on how to build prompts for Stable Diffusion, please [enter this link](https://beta.dreamstudio.ai/prompt-guide).

## 基于 stablediffusionapi 平台 [https://stablediffusionapi.com/docs/](https://stablediffusionapi.com/docs/)
或者 [Postman Collection](https://documenter.getpostman.com/view/18679074/2s83zdwReZ)

### 文字生成图片（Text to Image)
```php
use Imactool\HyperfStableDiffusion\StableDiffusion;

 $res = StableDiffusion::make()
                    ->useDreamboothApiV4()
                    ->withPayload('key', '')
                    ->withPayload('model_id', 'anything-v4')
                    ->withPayload('prompt', 'ultra realistic close up portrait ((beautiful pale cyberpunk female with heavy black eyeliner)), blue eyes, shaved side haircut, hyper detail, cinematic lighting, magic neon, dark red city, Canon EOS R3, nikon, f/1.4, ISO 200, 1/160s, 8K, RAW, unedited, symmetrical balance, in-frame, 8K')
                    ->withPayload('negative_prompt', 'painting, extra fingers, mutated hands, poorly drawn hands, poorly drawn face, deformed, ugly, blurry, bad anatomy, bad proportions, extra limbs, cloned face, skinny, glitchy, double torso, extra arms, extra hands, mangled fingers, missing lips, ugly face, distorted face, extra legs, anime')
                    ->withPayload('width', '512')
                    ->withPayload('height', '512')
                    ->withPayload('samples', '1')
                    ->withPayload('num_inference_steps', '30')
                    ->withPayload('seed', null)
                    ->withPayload('guidance_scale', '7.5')
                    ->withPayload('webhook', null)
                    ->withPayload('track_id', null)
                    ->text2img();

                var_dump( $res);

```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.