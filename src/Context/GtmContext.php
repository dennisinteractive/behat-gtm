<?php
namespace DennisDigital\Behat\Gtm\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Mink;
use Behat\Mink\WebAssert;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;


/**
 * Class GtmContext
 * @package DennisDigital\Behat\Gtm\Context
 */
class GtmContext implements MinkAwareContext {

  /**
   * @var Mink
   */
  private $mink;
  private $minkParameters;


  /**
   * Sets Mink instance.
   *
   * @param Mink $mink Mink session manager
   */
  public function setMink(Mink $mink) {
    $this->mink = $mink;
  }

  /**
   * Sets parameters provided for Mink.
   *
   * @param array $parameters
   */
  public function setMinkParameters(array $parameters) {
    $this->minkParameters = $parameters;
  }

  /**
   * Returns Mink instance.
   *
   * @return Mink
   */
  public function getMink() {
    if (null === $this->mink) {
      throw new \RuntimeException(
        'Mink instance has not been set on Mink context class. ' .
        'Have you enabled the Mink Extension?'
      );
    }

    return $this->mink;
  }

  /**
   * Returns Mink session assertion tool.
   *
   * @param string|null $name name of the session OR active session will be used
   *
   * @return WebAssert
   */
  public function assertSession($name = null) {
    return $this->getMink()->assertSession($name);
  }

  /**
   * Returns Mink session.
   *
   * @param string|null $name name of the session OR active session will be used
   *
   * @return Session
   */
  public function getSession($name = null) {
    return $this->getMink()->getSession($name);
  }

  /**
   * Check the google tag manager present in the page
   *
   * @Given google tag manager id is :arg1
   */
  public function tagManagerIdIs($id) {
    if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
      $this->assertSession()->responseContains("www.googletagmanager.com/gtm.js?id=$id");
    }
    else {
      $this->assertSession()->responseContains("www.googletagmanager.com/ns.html?id=$id");
    }
  }

  /**
   * Check google tag manager data layer contain key value pair
   *
   * @Given google tag manager data layer setting :arg1 should be :arg2
   */
  public function dataLayerSettingShouldBe($key, $value) {
    $property_value = $this->getDataLayerValue($key);
    if ($value != $property_value) {
      throw new \Exception($value . ' is not the same as ' . $property_value);
    }
  }

  /**
   * Check google tag manager data layer contain key value pair
   *
   * @Given google tag manager data layer setting :arg1 should match :arg2
   */
  public function getDataLayerSettingShouldMatch($key, $regex) {
    $property_value = $this->getDataLayerValue($key);
    if (!preg_match($regex, $property_value)) {
      throw new \Exception($property_value . ' does not match ' . $regex);
    }
  }

  /**
   * Get Google Tag Manager Data Layer value
   *
   * @param $key
   * @return mixed
   * @throws \Exception
   */
  protected function getDataLayerValue($key) {
    $json_arr = $this->getDataLayerJson();

    // Loop through the array and return the data layer value
    foreach ($json_arr as $json_item) {
      if (isset($json_item[$key])) {
        return $json_item[$key];
      }
    }
    throw new \Exception($key . ' not found.');
  }

  /**
   * Get dataLayer variable JSON.
   */
  protected function getDataLayerJson() {
    if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
      $json_arr = $this->getSession()->getDriver()->evaluateScript('return dataLayer;');
    }
    else {
      $json_arr = json_decode($this->getDataLayerJsonFromSource(), TRUE);
    }

    // If it's not an array throw an exception.
    if (!is_array($json_arr)) {
      throw new \Exception('dataLayer variable is not an array.');
    }

    return $json_arr;
  }

  /**
   * Get dataLayer variable JSON from raw source.
   */
  protected function getDataLayerJsonFromSource() {
    // Get the html.
    $html = $this->getSession()->getPage()->getContent();

    // Get the dataLayer json and json_decode it
    preg_match('~dataLayer\s*=\s*(.*?);</script>~' , $html, $match);
    if (!isset($match[0])) {
      throw new \Exception('dataLayer variable not found in source.');
    }

    return $match[1];
  }

  /**
   * Get dataLayer variable JSON from raw source.
   */
  protected function getDataLayerJsonFromJS() {
    $json_arr = $this->getSession()->getDriver()->evaluateScript('return dataLayer;');

    if (empty($json_arr)) {
      throw new \Exception('dataLayer variable not set on page.');
    }

    return $json_arr;
  }
}
