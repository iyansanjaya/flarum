<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if (!$schema->hasTable('seo_meta')) {
            $schema->create('seo_meta', function (Blueprint $table) {
                $table->increments('id');

                // Object information
                $table->integer('object_id');
                $table->string('object_type', 65535);

                // Make the combination Object ID and Object Type unique
                $table->unique(['object_id', 'object_type']);

                // Auto update data?
                $table->boolean('auto_update_data')->default(1);

                // Default HTML Tags
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->text('keywords')->nullable();

                // Robots
                $table->boolean('robots_noindex')->default(0);
                $table->boolean('robots_nofollow')->default(0);
                $table->boolean('robots_noarchive')->default(0);
                $table->boolean('robots_noimageindex')->default(0);
                $table->boolean('robots_nosnippet')->default(0);

                // Twitter tags
                $table->string('twitter_title')->nullable();
                $table->text('twitter_description')->nullable();
                $table->text('twitter_image')->nullable();
                $table->string('twitter_image_source')->nullable(); // Custom or Auto

                // Open Graph tags
                $table->string('open_graph_title')->nullable();
                $table->text('open_graph_description')->nullable();
                $table->text('open_graph_image')->nullable();
                $table->string('open_graph_image_source')->nullable(); // Custom or Auto

                // Extra
                $table->integer('estimated_reading_time')->nullable();

                // Row information
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();
            });
        }
    },
    'down' => function (Builder $schema) {
        $schema->drop('seo_meta');
    },
];
