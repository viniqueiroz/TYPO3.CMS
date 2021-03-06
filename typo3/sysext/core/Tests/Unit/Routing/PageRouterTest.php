<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\Tests\Unit\Routing;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3\CMS\Core\Routing\RouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PageRouterTest extends UnitTestCase
{
    /**
     * @test
     */
    public function properSiteConfigurationFindsRoute()
    {
        $incomingUrl = 'https://king.com/lotus-flower/en/mr-magpie/bloom';
        $pageRecord = ['uid' => 13, 'l10n_parent' => 0, 'slug' => '/mr-magpie/bloom/'];
        $site = new Site('lotus-flower', 13, [
            'base' => '/lotus-flower/',
            'languages' => [
                0 => [
                    'languageId' => 0,
                    'locale' => 'en_US.UTF-8',
                    'base' => '/en/'
                ],
            ]
        ]);
        $language = $site->getDefaultLanguage();

        $request = new ServerRequest($incomingUrl, 'GET');
        $subject = $this->getAccessibleMock(PageRouter::class, ['getPagesFromDatabaseForCandidates']);
        $subject->expects($this->once())->method('getPagesFromDatabaseForCandidates')->willReturn([$pageRecord]);
        $routeResult = $subject->matchRoute($request, '/mr-magpie/bloom', $site, $language);

        $expectedRouteResult = new RouteResult($request->getUri(), $site, $language, '', ['page' => $pageRecord, 'tail' => '']);
        $this->assertEquals($expectedRouteResult, $routeResult);
    }

    /**
     * Let's see if the slug is "/blabla" and the base does not have a trailing slash ("/en")
     * @test
     */
    public function properSiteConfigurationWithoutTrailingSlashFindsRoute()
    {
        $incomingUrl = 'https://king.com/lotus-flower/en/mr-magpie/bloom/unknown-code/';
        $pageRecord = ['uid' => 13, 'l10n_parent' => 0, 'slug' => '/mr-magpie/bloom/'];
        $site = new Site('lotus-flower', 13, [
            'base' => '/lotus-flower/',
            'languages' => [
                0 => [
                    'languageId' => 0,
                    'locale' => 'en_US.UTF-8',
                    'base' => '/en'
                ],
            ]
        ]);
        $language = $site->getDefaultLanguage();

        $request = new ServerRequest($incomingUrl, 'GET');
        $subject = $this->getAccessibleMock(PageRouter::class, ['getPagesFromDatabaseForCandidates']);
        $subject->expects($this->once())->method('getPagesFromDatabaseForCandidates')->willReturn([$pageRecord]);
        $routeResult = $subject->matchRoute($request, '/mr-magpie/bloom/unknown-code/', $site, $language);

        $expectedRouteResult = new RouteResult($request->getUri(), $site, $language, 'unknown-code/', ['page' => $pageRecord, 'tail' => 'unknown-code/']);
        $this->assertEquals($expectedRouteResult, $routeResult);
    }
}
