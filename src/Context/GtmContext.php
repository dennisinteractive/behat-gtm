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
   * Check the google tag manager noscript present in the page
   *
   * @Given google tag manager id is :arg1
   */
  public function googleTagManagerIdIs($id)
  {
    $this->jsWaitForDocumentLoaded();

    // script in head tag
    try {
      $script = '//www.googletagmanager.com/gtm.js?id=' . $id;
      $this->assertSession()->responseContains($script);
    } catch (Exception $e) {
      throw new Exception('google tag manager script "www.googletagmanager.com/gtm.js?id=' . $id . '" not found in head tag');
    }

    // noscript
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
    $this->jsWaitForDocumentLoaded();

    $script = <<<JS
        return (function(){
          for (var value in dataLayer) {
            for (var key in dataLayer[value]) {
              if("$key" == key && "$value" == dataLayer[value][key]) {
                return 1;
              }
            }
          }
          return 0;
        })();
JS;

    if (!$this->getSession()->evaluateScript($script)) {
      throw new \Exception(sprintf("google tag manager data layer setting '%s':'%s' not found", $key, $value));
    }
  }

}
