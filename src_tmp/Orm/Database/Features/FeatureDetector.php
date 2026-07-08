<?php
declare(strict_types=1);

namespace SaQle\Orm\Database\Features;

final class FeatureDetector {
    private array $features;

    public function __construct(string $version_string){
        $engine  = EngineDetector::detect($version_string);
        $version = VersionParser::normalize($version_string);

        $this->features = CapabilityRegistry::get($engine, $version);
    }

    public function supports(string $feature): bool{
        return $this->features[$feature] ?? false;
    }

    public function all(): array{
        return $this->features;
    }
}
