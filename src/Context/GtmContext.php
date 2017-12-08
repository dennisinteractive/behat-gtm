<?php
namespace DennisDigital\Behat\Gtm\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Mink\Mink;

/**
 * Class GtmContext
 * @package DennisDigital\Behat\Gtm\Context
 */
class GtmContext implements MinkAwareContext {

  /**
   * @var Mink
   */
  private $mink;

  /**
   * @inheritdoc
   */
  public function setMink(Mink $mink) {
    $this->mink = $mink;
  }

  /**
   * @inheritdoc
   */
  public function setMinkParameters(array $parameters) {
    // TODO: Implement setMinkParameters() method.
  }

  /**
   * Check the google tag manager present in the page
   *
   * @Given google tag manager id is :arg1
   */
  public function googleTagManagerIdIs($id)
  {
    $this->assertSession()->responseContains("www.googletagmanager.com/ns.html?id=$id");
  }

  /**
   * Check google tag manager data layer contain key value pair
   *
   * Requires @javascript tag on the scenario.
   *
   * @Given google tag manager data layer setting :arg1 should be :arg2
   */
  public function googleTagManagerDataLayerSettingShouldBe($key, $value) {
    return $this->theResponseShouldMatch(sprintf(
        '~dataLayer.*=.*%s\":\"%s\"~',
        $key,
        preg_quote($value)
      )
    );
  }

}
