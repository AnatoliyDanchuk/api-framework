<?php

namespace Framework\IntegratedService\Messenger\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\VarExporter\LazyObjectInterface;

class LazyServiceNormalizer extends PropertyNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        /** @var LazyObjectInterface $object */
        return parent::normalize($object->initializeLazyObject(), $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof LazyObjectInterface;
    }
}