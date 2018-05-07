<?php

namespace SilverStripe\MultiDomain;


use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataExtension;

/**
 * Class SiteTreeExtension
 * @package SilverStripe\MultiDomain
 *
 * @property SiteTree $owner
 */
class SiteTreeExtension extends DataExtension
{
    public function alternateAbsoluteLink($action = null)
    {
        // Get absolute vanity URL

        /** @var MultiDomainDomain $domain */
        foreach (MultiDomain::get_all_domains() as $domain) {
            if (!$domain->isActive()) {
                continue;
            }

            $url = $domain->getVanityURL($this->owner->Link($action));

            return Director::absoluteURL($url);

        }

        return Director::absoluteURL($this->owner->Link($action));
    }
}