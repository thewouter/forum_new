<?php
/**
 * @author Alexander Kim <alexander.k@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Site;

use Vanilla\Contracts\ConfigurationInterface;
use Vanilla\Contracts\Site\SiteSectionInterface;
use Vanilla\Contracts\Site\SiteSectionProviderInterface;

/**
 * Class SiteSectionModel
 * @package Vanilla\Site
 */
class SiteSectionModel {
    /** @var SiteSectionProviderInterface[] $providers */
    private $providers = [];

    /** @var SiteSectionInterface[] $siteSections */
    private $siteSections;

    /** @var SiteSectionInterface $currentSiteSection */
    private $currentSiteSection;

    /** @var SiteSectionInterface $currentSiteSection */
    private $defaultSiteSection;

    /**
     * SiteSectionModel constructor.
     *
     * @param ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config) {
        $this->defaultSiteSection = new DefaultSiteSection($config);
    }

    /**
     * Register site section
     *
     * @param SiteSectionProviderInterface $provider
     */
    public function addProvider(SiteSectionProviderInterface $provider) {
        $this->providers[] = $provider;
        if (!empty($current = $provider->getCurrentSiteSection())) {
            $this->currentSiteSection = $current;
        }
    }

    /**
     * Get all site sections that match a particular site section group.
     *
     * @param string $sectionGroupKey The name of the section group to check.
     * @return SiteSectionInterface[]
     */
    public function getForSectionGroup(string $sectionGroupKey): array {
        $siteSections = [];
        foreach ($this->getAll() as $siteSection) {
            if ($siteSection->getSectionGroup() === $sectionGroupKey) {
                $siteSections[] = $siteSection;
            }
        }
        return $siteSections;
    }

    /**
     * Returns all sections of the site.
     *
     * @return SiteSectionInterface[]
     */
    public function getAll(): array {
        if (empty($this->siteSections)) {
            $this->siteSections = [];
            foreach ($this->providers as $provider) {
                $this->siteSections = array_merge($this->siteSections, $provider->getAll());
            }
        }
        return $this->siteSections;
    }

    /**
     * Get a site section from it's base path.
     *
     * @param string $basePath
     * @return SiteSectionInterface|null
     */
    public function getByBasePath(string $basePath): ?SiteSectionInterface {
        /** @var SiteSectionInterface $siteSection */
        foreach ($this->getAll() as $siteSection) {
            if ($siteSection->getBasePath() === $basePath) {
                return $siteSection;
            }
        }
        return null;
    }

    /**
     * Get all site sections that match a particular locale.
     *
     * @param string $localeKey The locale key to lookup by.
     * @return SiteSectionInterface[]
     */
    public function getForLocale(string $localeKey): array {
        $siteSections = [];
        /** @var SiteSectionInterface $siteSection */
        foreach ($this->getAll() as $siteSection) {
            if ($localeKey === $siteSection->getContentLocale()) {
                $siteSections[] = $siteSection;
            }
        }
        return $siteSections;
    }

    /**
     * Get the current site section for the request automatically if possible.
     *
     * @return SiteSectionInterface
     */
    public function getCurrentSiteSection(): SiteSectionInterface {
        if (is_null($this->currentSiteSection)) {
            foreach ($this->providers as $provider) {
                if (!empty($current = $provider->getCurrentSiteSection())) {
                    $this->currentSiteSection = $current;
                }
            }
        }
        return $this->currentSiteSection ?? $this->defaultSiteSection;
    }
}
