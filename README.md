# imactool/hyperf-stable-diffusion

> 基于 `rulilg/laravel-stable-diffusion` 包直接平移并适配了 hyperf 框架。

> 我对它进行了一些改造，大部分功能保持了相同。在这里感谢一下 RuliLG ，实现了如此强大好用的 stable-diffusion 组件。


鸣谢：原作：[RuliLG](https://github.com/RuliLG)，特此鸣谢!

## 安装

```
composer require imactool/hyperf-stable-diffusion

```

## 发布配置（包含配置文件和迁移文件)

```
php bin/hyperf.php vendor:publish imactool/hyperf-stable-diffusion
php bin/hyperf.php migrate
```
至此，配置完成。

```
return [
    'url' => env('REPLICATE_URL', 'https://api.replicate.com/v1/predictions'),
    'token' => env('REPLICATE_TOKEN'),
    'version' => env('REPLICATE_STABLEDIFFUSION_VERSION', 'db21e45d3f7023abc2a46ee38a23973f6dce16bb082a930b0c49861f96d1e5bf'),
];

```

然后在 `.env` 里增加 [Replicate](https://replicate.com/) 的 `token` 到 `REPLICATE_TOKEN`即可。

## 使用

### 生成
```
use Imactool\HyperfStableDiffusion\Prompt;
use Imactool\HyperfStableDiffusion\StableDiffusion;

   $result = StableDiffusion::make()->withPrompt(
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

### 查询结果

```
use Imactool\HyperfStableDiffusion\StableDiffusion;
 $freshResults = StableDiffusion::get($replicate_id);

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


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.