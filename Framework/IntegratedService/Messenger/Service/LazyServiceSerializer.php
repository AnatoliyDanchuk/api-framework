<?php

namespace Framework\IntegratedService\Messenger\Service;

use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

class LazyServiceSerializer extends Serializer
{
    public function __construct(SymfonySerializerInterface $serializer = null, string $format = 'json', array $context = [])
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new LazyServiceNormalizer(defaultContext: [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return [];
                },
            ]),
            new ObjectNormalizer(),
        ];
        $serializer = new SymfonySerializer($normalizers, $encoders);

        parent::__construct($serializer, $format, $context);
    }
}