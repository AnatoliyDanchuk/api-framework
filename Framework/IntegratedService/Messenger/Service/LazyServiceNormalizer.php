<?php

namespace Framework\IntegratedService\Messenger\Service;

use Aws\S3\S3Client;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\VarExporter\LazyObjectInterface;

class LazyServiceNormalizer extends PropertyNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        // Memory limit when sending service to messenger.There are not necessary to send it.
        if ($object instanceof S3Client) {
            return null;
        }

        if ($object instanceof LazyObjectInterface) {
            $object = $object->initializeLazyObject();
        }

        return array_merge(
            parent::normalize($object, $format, $context),
            ['ServiceNormalizer_class' => get_class($object)],
        );
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof LazyObjectInterface || is_object($data);
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        $type = $data['ServiceNormalizer_class'];
        unset($data['ServiceNormalizer_class']);

        return parent::denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return interface_exists($type) && array_key_exists('ServiceNormalizer_class', $data);
    }
}