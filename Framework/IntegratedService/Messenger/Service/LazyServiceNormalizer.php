<?php

namespace Framework\IntegratedService\Messenger\Service;

use Aws\S3\S3Client;
use Symfony\Component\PropertyAccess\Exception\UninitializedPropertyException;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\VarExporter\LazyObjectInterface;

class LazyServiceNormalizer extends AbstractObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private PropertyNormalizer $propertyNormalizer;

    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, ?PropertyTypeExtractorInterface $propertyTypeExtractor = null, ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null, callable $objectClassResolver = null, array $defaultContext = [])
    {
        $this->propertyNormalizer = new PropertyNormalizer($classMetadataFactory, $nameConverter, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);
        parent::__construct($classMetadataFactory, $nameConverter, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);
        if (!isset($this->defaultContext[PropertyNormalizer::NORMALIZE_VISIBILITY])) {
            $this->defaultContext[PropertyNormalizer::NORMALIZE_VISIBILITY] = PropertyNormalizer::NORMALIZE_PUBLIC | PropertyNormalizer::NORMALIZE_PROTECTED | PropertyNormalizer::NORMALIZE_PRIVATE;
        }
    }

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

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $type = $data['ServiceNormalizer_class'];
        unset($data['ServiceNormalizer_class']);

        return parent::denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return interface_exists($type) && array_key_exists('ServiceNormalizer_class', $data ?? []);
    }

    protected function extractAttributes(object $object, string $format = null, array $context = []): array
    {
        $reflectionObject = new \ReflectionObject($object);
        $attributes = [];

        do {
            foreach ($reflectionObject->getProperties() as $property) {
                if (!$this->isAllowedAttribute($reflectionObject->getName(), $property->name, $format, $context)) {
                    continue;
                }

                $attributes[] = $property->name;
            }
        } while ($reflectionObject = $reflectionObject->getParentClass());

        return array_unique($attributes);
    }

    protected function getAttributeValue(object $object, string $attribute, string $format = null, array $context = []): mixed
    {
        try {
            $reflectionProperty = $this->getReflectionProperty($object, $attribute);
        } catch (\ReflectionException) {
            return null;
        }

        if ($reflectionProperty->hasType()) {
            return $reflectionProperty->getValue($object);
        }

        if (!method_exists($object, '__get') && !isset($object->$attribute)) {
            $propertyValues = (array) $object;

            if (($reflectionProperty->isPublic() && !\array_key_exists($reflectionProperty->name, $propertyValues))
                || ($reflectionProperty->isProtected() && !\array_key_exists("\0*\0{$reflectionProperty->name}", $propertyValues))
                || ($reflectionProperty->isPrivate() && !\array_key_exists("\0{$reflectionProperty->class}\0{$reflectionProperty->name}", $propertyValues))
            ) {
                throw new UninitializedPropertyException(sprintf('The property "%s::$%s" is not initialized.', $object::class, $reflectionProperty->name));
            }
        }

        return $reflectionProperty->getValue($object);
    }

    protected function setAttributeValue(object $object, string $attribute, mixed $value, string $format = null, array $context = []): void
    {
        try {
            $reflectionProperty = $this->getReflectionProperty($object, $attribute);
        } catch (\ReflectionException) {
            return;
        }

        if ($reflectionProperty->isStatic()) {
            return;
        }

        $reflectionProperty->setValue($object, $value);
    }

    private function getReflectionProperty(string|object $classOrObject, string $attribute): \ReflectionProperty
    {
        $reflectionClass = new \ReflectionClass($classOrObject);
        while (true) {
            try {
                return $reflectionClass->getProperty($attribute);
            } catch (\ReflectionException $e) {
                if (!$reflectionClass = $reflectionClass->getParentClass()) {
                    throw $e;
                }
            }
        }
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->propertyNormalizer->getSupportedTypes($format);
    }

    protected function isAllowedAttribute(object|string $classOrObject, string $attribute, string $format = null, array $context = []): bool
    {
        if (!parent::isAllowedAttribute($classOrObject, $attribute, $format, $context)) {
            return false;
        }

        try {
            $reflectionProperty = $this->getReflectionProperty($classOrObject, $attribute);
        } catch (\ReflectionException) {
            return false;
        }

        if ($reflectionProperty->isStatic()) {
            return false;
        }

        $normalizeVisibility = $context[PropertyNormalizer::NORMALIZE_VISIBILITY] ?? $this->defaultContext[PropertyNormalizer::NORMALIZE_VISIBILITY];

        if ((PropertyNormalizer::NORMALIZE_PUBLIC & $normalizeVisibility) && $reflectionProperty->isPublic()) {
            return true;
        }

        if ((PropertyNormalizer::NORMALIZE_PROTECTED & $normalizeVisibility) && $reflectionProperty->isProtected()) {
            return true;
        }

        if ((PropertyNormalizer::NORMALIZE_PRIVATE & $normalizeVisibility) && $reflectionProperty->isPrivate()) {
            return true;
        }

        return false;
    }
}