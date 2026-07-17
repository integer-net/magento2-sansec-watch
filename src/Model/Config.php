<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use IntegerNet\SansecWatch\Model\Exception\InvalidConfigurationException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Uid\Uuid;

use function sprintf;
use function __;

class Config implements GetApiUrlInterface
{
    public const string POLICY_TABLE = 'integernet_sansecwatch_policies';

    private const string GENERAL_ENABLED                   = 'integernet_sansecwatch/general/enabled';
    private const string GENERAL_ID                        = 'integernet_sansecwatch/general/id';
    private const string FPC_MODE                          = 'integernet_sansecwatch/fpc/mode';
    private const string DIRECTIVE_DEFAULT_SETTING_PATTERN = 'integernet_sansecwatch/directive/%s';
    private const string DIRECTIVE_SETTING_PATTERN         = 'integernet_sansecwatch/directive/%s/%s';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::GENERAL_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function getId(?int $storeId = null): Uuid
    {
        $id = (string) $this->scopeConfig->getValue(self::GENERAL_ID, ScopeInterface::SCOPE_STORE, $storeId);

        if (!Uuid::isValid($id)) {
            throw InvalidConfigurationException::fromInvalidUuid($id);
        }

        return Uuid::fromString($id);
    }

    public function getFpcMode(?int $storeId = null): FpcMode
    {
        $mode = $this->scopeConfig->getValue(self::FPC_MODE, ScopeInterface::SCOPE_STORE, $storeId);

        return FpcMode::tryFrom($mode) ?? FpcMode::None;
    }

    public function getApiUrl(): string
    {
        return 'https://sansec.watch/api/magento/{id}.json';
    }

    public function getDefaultDirectiveSetting(DirectiveFlag $flag, ?int $storeId = null): bool
    {
        $configPath = sprintf(self::DIRECTIVE_DEFAULT_SETTING_PATTERN, $flag->value);

        return $this->scopeConfig->isSetFlag($configPath, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @throws LocalizedException
     */
    public function getDirectiveSetting(Directive $directive, DirectiveFlag $flag, ?int $storeId = null): bool
    {
        $configPath = sprintf(
            self::DIRECTIVE_SETTING_PATTERN,
            $directive->configKey(),
            $flag->value,
        );

        $value = (string) $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE, $storeId);
        $value = DirectiveSetting::tryFrom($value)
            ?? throw new LocalizedException(
                __('Invalid value for directive setting %1 on %2', $flag->value, $directive->value),
            );

        return match ($value) {
            DirectiveSetting::Yes       => true,
            DirectiveSetting::No        => false,
            DirectiveSetting::Inherited => $this->getDefaultDirectiveSetting($flag, $storeId),
        };
    }
}
