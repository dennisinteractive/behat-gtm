<?php
namespace DennisDigital\Behat\Gtm\Context;

use DennisDigital\BDDCommonExtension\Context\RegisteredContexts;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;


/**
 * Class GtmContext
 * @package DennisDigital\Behat\Gtm\Context
 */
class GtmContext implements Context {

  /**
   * Drupal context.
   */
  private $drupalContext;

  /**
   * @BeforeScenario
   *
   * @param BeforeScenarioScope $scope
   */
  public function beforeScenario(BeforeScenarioScope $scope) {
    // Get the environment.
    $environment = $scope->getEnvironment();

    // Get all the contexts we need.
    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');

    // Get all the registered contexts.
    $classes = RegisteredContexts::get();
    foreach ($classes as $name => $class) {
      $this->contexts[$name] = $environment->getContext($class);
    }

    // Only error on these levels.
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

  }

  /**
   * Wrapper for drupal extension.
   *
   * @return mixed
   */
  private function getSession() {
    return $this->drupalContext->getSession();
  }

  /**
   * Wrapper for drupal extension.
   *
   * @return mixed
   */
  private function assertSession() {
    return $this->drupalContext->assertSession();
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
   * @Given google tag manager data layer setting :arg1 should be :arg2
   */
  public function googleTagManagerDataLayerSettingShouldBe($key, $value) {
    $propertyValue = $this->googleTagManagerGetDataLayerValue($key);
    if ($value != $propertyValue) {
      throw new \Exception($value . ' is not the same as ' . $propertyValue);
    }
  }
  /**
   * Check google tag manager data layer contain key value pair
   *
   * @Given google tag manager data layer setting :arg1 should match :arg2
   */
  public function googleTagManagerDataLayerSettingShouldMatch($key, $regex) {
    $propertyValue = $this->googleTagManagerGetDataLayerValue($key);
    if (!preg_match($regex, $propertyValue)) {
      throw new \Exception($propertyValue . ' does not match ' . $regex);
    }
  }
  /**
   * Get Google Tag Manager Data Layer value
   *
   * @param $key
   * @return mixed
   * @throws \Exception
   */
  protected function googleTagManagerGetDataLayerValue($key) {
    // Get the html
    $html = $this->getSession()->getPage()->getContent();
    // Get the dataLayer json and json_decode it
    preg_match('~dataLayer\s*=\s*(.*?);</script>~' , $html, $match);
    if (!isset($match[0])) {
      throw new \Exception('dataLayer variable not found.');
    }
    $jsonArr = json_decode($match[1]);
    // If it's not an array throw an exception
    if (!is_array($jsonArr)) {
      throw new \Exception('dataLayer variable is not an array.');
    }
    // Loop through the array and return the data layer value
    foreach ($jsonArr as $jsonObj) {
      if (isset($jsonObj->{$key})) {
        return $jsonObj->{$key};
      }
    }
    throw new \Exception($key . ' not found.');
  }
}
