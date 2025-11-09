<?php
declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\MappingProperties;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateIndexProductV1 implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::create('product_v1', function (Mapping $mapping, Settings $settings) {
            $settings->analysis([
                'normalizer' => [

                    'lowercase_normalizer' => [
                        'type' => 'custom',
                        'filter' => ['lowercase', 'asciifolding'],
                    ],
                ],
                'filter' => [

                    'ru_stop'      => ['type' => 'stop',    'stopwords' => '_russian_'],
                    'ru_stemmer'   => ['type' => 'stemmer', 'language'  => 'russian'],
                    'en_stemmer'   => ['type' => 'stemmer', 'language'  => 'english'],

                    'trigram_filter' => ['type' => 'ngram',      'min_gram' => 3, 'max_gram' => 3],
                    'edge_2_20'      => ['type' => 'edge_ngram', 'min_gram' => 2, 'max_gram' => 20],

                    'ru_en_synonyms' => [
                        'type' => 'synonym_graph',
                        'lenient' => true,
                        'synonyms' => [],
                    ],
                ],
                'analyzer' => [

                    'ru_en_index' => [
                        'type'      => 'custom',
                        'tokenizer' => 'standard',
                        'filter'    => ['lowercase', 'asciifolding', 'ru_stop', 'ru_stemmer', 'en_stemmer'],
                    ],

                    'ru_en_search' => [
                        'type'      => 'custom',
                        'tokenizer' => 'standard',
                        'filter'    => ['lowercase', 'asciifolding', 'ru_stop', 'ru_stemmer', 'en_stemmer', 'ru_en_synonyms'],
                    ],

                    'ru_en_trigram' => [
                        'type'      => 'custom',
                        'tokenizer' => 'standard',
                        'filter'    => ['lowercase', 'asciifolding', 'trigram_filter'],
                    ],

                    'ru_en_autocomplete' => [
                        'type'      => 'custom',
                        'tokenizer' => 'standard',
                        'filter'    => ['lowercase', 'asciifolding', 'edge_2_20'],
                    ],
                ],
            ]);

            $settings->index([
                'number_of_shards'   => 1,
                'number_of_replicas' => 0,
                'max_ngram_diff' => 50,
                'refresh_interval' => '30s',
            ]);


            $mapping->long('id');

            $mapping->text('title', [
                'analyzer'        => 'ru_en_index',
                'search_analyzer' => 'ru_en_search',
                'fields' => [
                    'keywords' => [
                        'type' => 'keyword',
                        'normalizer' => 'lowercase_normalizer',
                    ],
                    'trigram' => [
                        'type' => 'text',
                        'analyzer' => 'ru_en_trigram',
                        'search_analyzer' => 'ru_en_search',
                    ],
                    'autocomplete' => [
                        'type' => 'text',
                        'analyzer' => 'ru_en_autocomplete',
                        'search_analyzer' => 'ru_en_search',
                    ],
                ],
            ]);

            $mapping->text('slug', [
                'analyzer'        => 'ru_en_index',
                'search_analyzer' => 'ru_en_search',
                'fields' => [
                    'keywords' => [
                        'type' => 'keyword',
                        'normalizer' => 'lowercase_normalizer',
                    ],
                ],
            ]);

            $mapping->date('created_at_timestamp');
            $mapping->keyword('created_at');


            $mapping->keyword('preview_picture');

            $mapping->text('description', [
                'analyzer'        => 'ru_en_index',
                'search_analyzer' => 'ru_en_search',
                'fields' => [
                    'trigram' => [
                        'type' => 'text',
                        'analyzer' => 'ru_en_trigram',
                        'search_analyzer' => 'ru_en_search',
                    ],
                    'autocomplete' => [
                        'type' => 'text',
                        'analyzer' => 'ru_en_autocomplete',
                        'search_analyzer' => 'ru_en_search',
                    ],
                ],
            ]);

            $mapping->nested('prices', function (MappingProperties $properties) {
                $properties->long('id');
                $properties->keyword('name');
                $properties->double('price');
                return ['properties' => $properties->toArray()];
            });

            $mapping->nested('stocks', function (MappingProperties $properties) {
                $properties->long('id');
                $properties->keyword('title');
                $properties->integer('quantity');
                return ['properties' => $properties->toArray()];
            });

        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('product_v1');
    }
}
