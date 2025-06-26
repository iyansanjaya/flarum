<?php

namespace V17Development\FlarumSeo\SeoMeta\Commands;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use V17Development\FlarumSeo\SeoMeta\SeoMeta;

class UpdateSeoMetaHandler
{
    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Handle language update
     */
    public function handle(UpdateSeoMeta $command)
    {
        $command->actor->assertAdmin();

        $seoMeta = SeoMeta::findOrFail($command->id);

        $seoMeta->auto_update_data = Arr::get($command->data, 'attributes.autoUpdateData', true);
        $seoMeta->title = Arr::get($command->data, 'attributes.title');
        $seoMeta->description = Arr::get($command->data, 'attributes.description');
        $seoMeta->keywords = Arr::get($command->data, 'attributes.keywords');

        // Robots
        $seoMeta->robots_noindex = Arr::get($command->data, 'attributes.robotsNoindex', false);
        $seoMeta->robots_nofollow = Arr::get($command->data, 'attributes.robotsNofollow', false);
        $seoMeta->robots_noarchive = Arr::get($command->data, 'attributes.robotsNoarchive', false);
        $seoMeta->robots_noimageindex = Arr::get($command->data, 'attributes.robotsNoimageindex', false);
        $seoMeta->robots_nosnippet = Arr::get($command->data, 'attributes.robotsNosnippet', false);

        // Twitter tags
        $seoMeta->twitter_title = Arr::get($command->data, 'attributes.twitterTitle', null);
        $seoMeta->twitter_description = Arr::get($command->data, 'attributes.twitterDescription', null);
        $seoMeta->twitter_image = Arr::get($command->data, 'attributes.twitterImage', null);
        $seoMeta->twitter_image_source = Arr::get($command->data, 'attributes.twitterImageSource', null);

        // Open graph tags
        $seoMeta->open_graph_title = Arr::get($command->data, 'attributes.openGraphTitle', null);
        $seoMeta->open_graph_description = Arr::get($command->data, 'attributes.openGraphDescription', null);
        $seoMeta->open_graph_image = Arr::get($command->data, 'attributes.openGraphImage', null);
        $seoMeta->open_graph_image_source = Arr::get($command->data, 'attributes.openGraphImageSource', null);

        // Estimated reading time
        $seoMeta->estimated_reading_time = Arr::get($command->data, 'attributes.estimatedReadingTime', null);

        // Save changes
        $seoMeta->save();

        return $seoMeta;
    }
}
