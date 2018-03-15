<?php

namespace SilverStripe\MultiDomain;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\HTTPMiddleware;

/**
 * A request filter for handling multi-domain logic
 *
 * @package  silverstripe-multi-domain
 * @author  Aaron Carlino <aaron@silverstripe.com>
 */
class MultiDomainRequestFilter implements HTTPMiddleware
{

    /**
     * Gets the active domain, and sets its URL to the native one, with a vanity
     * URL in the request
     *
     * @param  HTTPRequest $request
     * @param callable     $delegate
     * @return HTTPResponse
     */
    public function process(HTTPRequest $request, callable $delegate)
    {
        if (Director::is_cli()) {
            $response = $delegate($request);
            return $response;
        }

        // Not the best place for validation, but _config.php is too early.
        if (!MultiDomain::get_primary_domain()) {
            return new HTTPResponse( 'MultiDomain must define a "' . MultiDomain::KEY_PRIMARY . '" domain in the config, under "domains"', 400);
        }

        $response = $delegate($request);

        foreach (MultiDomain::get_all_domains() as $domain) {
            if (!$domain->isActive()) {
                continue;
            }

            $url = $this->createNativeURLForDomain($domain);
            $parts = explode('?', $url);
            $request->setURL($parts[0]);

            $response = $delegate($request);
        }

        return $response;
    }

    /**
     * Creates a native URL for a domain. This functionality is abstracted so
     * that other modules can overload it, e.g. translatable modules that
     * have their own custom URLs.
     *
     * @param  MultiDomainDomain $domain
     * @return string
     */
    protected function createNativeURLForDomain(MultiDomainDomain $domain)
    {
        return Controller::join_links(
            Director::baseURL(),
            $domain->getNativeURL($domain->getRequestUri())
        );
    }
}
