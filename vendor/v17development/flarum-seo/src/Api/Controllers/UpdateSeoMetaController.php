<?php

namespace V17Development\FlarumSeo\Api\Controllers;

use Flarum\Api\Controller\AbstractShowController;
use Illuminate\Contracts\Bus\Dispatcher;
use Flarum\Foundation\DispatchEventsTrait;
use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use V17Development\FlarumSeo\Api\Serializers\SeoMetaSerializer;
use V17Development\FlarumSeo\SeoMeta\Commands\UpdateSeoMeta;

class UpdateSeoMetaController extends AbstractShowController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = SeoMetaSerializer::class;

    public function __construct(private Dispatcher $events) {}

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $id = Arr::get($request->getQueryParams(), 'id');
        $data = Arr::get($request->getParsedBody(), 'data', false);

        return $this->events->dispatch(
            new UpdateSeoMeta($actor, intval($id), $data)
        );
    }
}
