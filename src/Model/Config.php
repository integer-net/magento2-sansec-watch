<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\Exception\InvalidConfigurationException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Symfony\Component\Uid\Uuid;

class Config
{
    public const POLICY_TABLE = 'integernet_sansecwatch_policies';

    public const INTEGERNET_SANSEC_WATCH_GENERAL_ENABLED = 'integernet_sansecwatch/general/enabled';
    public const INTEGERNET_SANSEC_WATCH_GENERAL_ID      = 'integernet_sansecwatch/general/id';
    public const INTEGERNET_SANSEC_WATCH_FPC_MODE        = 'integernet_sansecwatch/fpc/mode';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::INTEGERNET_SANSEC_WATCH_GENERAL_ENABLED);
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function getId(): Uuid
    {
        $id = $this->scopeConfig->getValue(self::INTEGERNET_SANSEC_WATCH_GENERAL_ID);

        if (!Uuid::isValid($id)) {
            throw InvalidConfigurationException::fromInvalidUuid($id);
        }

        return Uuid::fromString($id);
    }

    public function getFpcMode(): FpcMode
    {
        $mode = $this->scopeConfig->getValue(self::INTEGERNET_SANSEC_WATCH_FPC_MODE);

        return FpcMode::tryFrom($mode) ?? FpcMode::NONE;
    }
}
